import { Injectable, computed, inject, signal } from '@angular/core';
import { firstValueFrom } from 'rxjs';

import {
  Category,
  Engineer,
  HelpInfo,
  Owner,
  ParameterDataType,
  ParameterValue,
  PrimaryCategory,
  Procedure,
  ProcedurePayload,
  Standard,
  TertiaryDetail,
  UploadedFileInfo,
} from '../../core/models/procedure.models';
import { CatalogService } from '../../core/services/catalog.service';
import { ProcedureService } from '../../core/services/procedure.service';
import { ApiError, toApiError } from '../../core/utils/api-error';
import { CaratulaData } from '../../shared/caratula/caratula';
import { STEP_HELP, helpFromInfo } from './help-content';
import { AddressField, AddressFields, DraftRef, EngineerItem, HelpContent, OwnerItem, StepId, addressFieldOf } from './wizard.models';

const DRAFT_KEY = 'sib.draft';

/** Campos del backend → paso donde se corrigen (para los errores 422) */
const FIELD_STEP: [prefix: string, step: StepId][] = [
  ['title', 2],
  ['primary_category_id', 1],
  ['secondary_category_id', 1],
  ['tertiary_category_id', 1],
  ['topic_name', 1],
];

/**
 * Estado del asistente de trámites (uno por pantalla del asistente).
 * Los componentes de cada paso leen y modifican el estado solo a través de este store.
 */
@Injectable()
export class WizardStore {
  private readonly catalog = inject(CatalogService);
  private readonly procedures = inject(ProcedureService);

  /* ============================================================
   |  Navegación
   * ============================================================ */
  readonly step = signal<StepId>(1);

  /* ============================================================
   |  Catálogos
   * ============================================================ */
  readonly primaries = signal<PrimaryCategory[]>([]);
  readonly secondaries = signal<Category[]>([]);
  readonly tertiaries = signal<Category[]>([]);
  readonly tertiaryDetail = signal<TertiaryDetail | null>(null);
  readonly standardsCatalog = signal<Standard[]>([]);

  readonly loadingBase = signal(true);
  readonly loadingSecondaries = signal(false);
  readonly loadingTertiaries = signal(false);
  readonly loadingDetail = signal(false);
  readonly catalogError = signal<string | null>(null);

  /* ============================================================
   |  Selección del usuario
   * ============================================================ */
  readonly title = signal('');
  readonly primary = signal<PrimaryCategory | null>(null);
  readonly secondary = signal<Category | null>(null);
  readonly tertiary = signal<Category | null>(null);
  readonly topicName = signal('');
  /** Valor de cada parámetro (según su tipo de dato) */
  readonly values = signal<Record<number, ParameterValue>>({});
  readonly standardIds = signal<number[]>([]);
  readonly owners = signal<OwnerItem[]>([]);
  readonly projectists = signal<EngineerItem[]>([]);
  readonly address = signal('');
  readonly zone = signal('');
  readonly municipality = signal('');
  /** Punto elegido en el mapa */
  readonly latitude = signal<number | null>(null);
  readonly longitude = signal<number | null>(null);

  /* ============================================================
   |  Trámite guardado (borrador o enviado)
   * ============================================================ */
  readonly procedure = signal<Procedure | null>(null);
  /** Hubo cambios en pasos 1-2 después de guardar: hay que volver a guardar al salir del paso 2 */
  readonly dirty = signal(false);
  readonly saving = signal(false);
  readonly saveError = signal<ApiError | null>(null);
  readonly submitting = signal(false);
  readonly submitError = signal<ApiError | null>(null);
  readonly resuming = signal(false);
  readonly draftRef = signal<DraftRef | null>(readDraft());

  /* ============================================================
   |  Panel de ayuda
   * ============================================================ */
  /** Ayuda "fijada" al hacer clic/enfocar un elemento */
  readonly pinnedHelp = signal<HelpContent | null>(null);
  /** Ayuda temporal al pasar el mouse */
  readonly hoverHelp = signal<HelpContent | null>(null);
  readonly helpTab = signal<'ayuda' | 'caratula'>('ayuda');
  /** Hoja de ayuda abierta en móvil */
  readonly helpOpen = signal(false);

  readonly activeHelp = computed<HelpContent>(
    () => this.hoverHelp() ?? this.pinnedHelp() ?? STEP_HELP[this.step()],
  );

  /* ============================================================
   |  Derivados
   * ============================================================ */
  readonly isInformes = computed(() => !!this.primary()?.is_special);
  readonly parameters = computed(() => this.tertiaryDetail()?.parameters ?? []);
  /** Parámetros que se llenan a mano (todo menos los direccionales) */
  readonly formParameters = computed(() => this.parameters().filter((p) => p.parameter_type !== 'direccional'));
  /** Calle, dirección, zona y municipio: se llenan desde el mapa */
  readonly addressParameters = computed(() => this.parameters().filter((p) => p.parameter_type === 'direccional'));
  /** Documentos que el usuario debe entregar: los descarga aquí y los sube llenos en el paso 3 */
  readonly downloads = computed(() => this.tertiaryDetail()?.required_documents ?? []);
  readonly isLocked = computed(() => !!this.procedure() && !this.procedure()!.is_draft);
  /** Documentos requeridos del borrador que aún no tienen archivo (bloquean la vista previa y el envío) */
  readonly missingDocuments = computed(() => this.procedure()?.required_documents.filter((d) => !d.files.length) ?? []);


  /** Qué falta para pasar del paso 1 (se muestra al usuario como checklist) */
  readonly step1Missing = computed(() => {
    const missing: string[] = [];
    if (!this.primary()) missing.push('Selecciona la categoría principal');
    if (!this.secondary()) missing.push('Selecciona la categoría secundaria');
    if (this.isInformes()) {
      if (this.topicName().trim().length < 3) missing.push('Escribe el tema del informe (mínimo 3 letras)');
    } else if (!this.tertiary()) {
      missing.push('Selecciona el tipo de proyecto');
    }
    return missing;
  });

  readonly step2Missing = computed(() => {
    const missing: string[] = [];
    if (this.title().trim().length < 3) missing.push('Escribe el título del proyecto (mínimo 3 letras)');
    const v = this.values();
    const pending = this.parameters().filter((p) => p.is_required && isEmptyValue(v[p.id]));
    if (pending.length === 1) {
      missing.push(`Completa el parámetro "${pending[0].name}"`);
    } else if (pending.length > 1) {
      missing.push(`Completa los ${pending.length} parámetros obligatorios pendientes`);
    }
    if (!this.owners().length) missing.push('Agrega al menos un interesado (propietario)');
    if (!this.projectists().length) missing.push('Agrega al menos un ingeniero proyectista');
    return missing;
  });

  readonly typeName = computed(() =>
    this.isInformes() ? this.topicName().trim().toUpperCase() || null : this.tertiary()?.name ?? null,
  );

  /** Datos de la carátula: en vivo mientras se edita, o los guardados si ya se envió */
  readonly caratula = computed<CaratulaData>(() => {
    const p = this.procedure();
    if (p && this.isLocked()) {
      return {
        primaryName: p.primary_category.name,
        primaryLogo: p.primary_category.logo,
        secondaryName: p.secondary_category.name,
        tertiaryCode: p.tertiary_category?.code ?? null,
        typeName: p.tertiary_category?.name ?? p.topic?.name ?? null,
        title: p.title,
        parameters: p.parameters.map((x) => ({
          name: x.name,
          value: formatParameterValue(x.value, x.data_type),
          unit: x.unit_of_measure,
        })),
        standards: p.standards.map((s) => s.name),
        owners: p.owners.map((o) => o.name),
        engineers: p.projectists.map((e) => ({ name: e.full_name, rni: e.rni })),
      };
    }

    const v = this.values();
    const standards = this.standardsCatalog();
    return {
      primaryName: this.primary()?.name ?? null,
      primaryLogo: this.primary()?.logo ?? null,
      secondaryName: this.secondary()?.name ?? null,
      tertiaryCode: this.isInformes() ? null : this.tertiary()?.code ?? null,
      typeName: this.typeName(),
      title: this.title().trim().toUpperCase(),
      // Todos los parámetros, también los opcionales vacíos (quedan en blanco para llenarse a mano)
      parameters: this.parameters().map((x) => ({
          name: x.name,
          value: formatParameterValue(v[x.id] ?? null, x.data_type),
          unit: x.unit_of_measure,
        })),
      standards: this.standardIds()
        .map((id) => standards.find((s) => s.id === id)?.name)
        .filter((n): n is string => !!n),
      owners: principalFirst(this.owners()).map((o) => o.name),
      engineers: principalFirst(this.projectists()).map((e) => ({ name: e.full_name, rni: e.rni })),
    };
  });

  /* ============================================================
   |  Inicio
   * ============================================================ */
  private basePromise: Promise<void> | null = null;

  init(hash: string | null): void {
    void this.loadBase();
    if (hash) void this.resume(hash);
  }

  loadBase(): Promise<void> {
    if (!this.basePromise) {
      this.loadingBase.set(true);
      this.catalogError.set(null);
      this.basePromise = Promise.all([
        firstValueFrom(this.catalog.primaryCategories()).then((v) => this.primaries.set(v)),
        firstValueFrom(this.catalog.standards()).then((v) => this.standardsCatalog.set(v)),
      ])
        .then(() => undefined)
        .catch((err) => {
          this.basePromise = null;
          this.catalogError.set(toApiError(err).message);
        })
        .finally(() => this.loadingBase.set(false));
    }
    return this.basePromise;
  }

  /** Retoma un trámite por su hash (enlace o borrador recordado) */
  async resume(hash: string): Promise<void> {
    this.resuming.set(true);
    try {
      const p = await firstValueFrom(this.procedures.get(hash));
      if (!p.is_draft) {
        this.procedure.set(p);
        this.forgetDraft();
        this.step.set(5);
        return;
      }
      await this.hydrate(p);
      this.step.set(3);
    } catch (err) {
      const e = toApiError(err);
      this.forgetDraft();
      this.catalogError.set(e.status === 404 ? 'No encontramos ese trámite. Puedes iniciar uno nuevo.' : e.message);
    } finally {
      this.resuming.set(false);
    }
  }

  /** Vuelve a llenar los pasos 1-2 con un borrador guardado */
  private async hydrate(p: Procedure): Promise<void> {
    await this.loadBase();

    this.procedure.set(p);
    this.title.set(p.title);

    this.primary.set(this.primaries().find((x) => x.id === p.primary_category.id) ?? null);
    this.secondaries.set(await firstValueFrom(this.catalog.secondaryCategories(p.primary_category.id)));
    this.secondary.set(this.secondaries().find((x) => x.id === p.secondary_category.id) ?? null);

    if (p.tertiary_category) {
      this.tertiaries.set(await firstValueFrom(this.catalog.tertiaryCategories(p.secondary_category.id)));
      this.tertiary.set(this.tertiaries().find((x) => x.id === p.tertiary_category!.id) ?? null);
      this.tertiaryDetail.set(await firstValueFrom(this.catalog.tertiaryDetail(p.tertiary_category.id)));
    }
    this.topicName.set(p.topic?.name ?? '');

    const v: Record<number, ParameterValue> = {};
    for (const param of this.parameters()) {
      v[param.id] = p.parameters.find((x) => x.parameter_id === param.id)?.value ?? null;
    }
    this.values.set(v);

    this.standardIds.set(p.standards.map((s) => s.id));
    this.owners.set(p.owners.map((o) => ({ id: o.id, name: o.name, phone: o.phone, is_principal: o.is_principal })));
    this.projectists.set(
      p.projectists.map((e) => ({ id: e.id, full_name: e.full_name, rni: e.rni, ci: e.ci, specialties: [], is_principal: e.is_principal })),
    );
    this.address.set(p.address ?? '');
    this.zone.set(p.zone ?? '');
    this.municipality.set(p.municipality ?? '');
    this.latitude.set(p.latitude !== null ? Number(p.latitude) : null);
    this.longitude.set(p.longitude !== null ? Number(p.longitude) : null);
    this.dirty.set(false);
  }

  /* ============================================================
   |  Paso 1: categorías
   * ============================================================ */
  async selectPrimary(p: PrimaryCategory): Promise<void> {
    this.pinHelp(
      helpFromInfo(p, 'Categoría principal', '🏛️', {
        warning: p.is_special
          ? 'INFORMES no tiene tipos de proyecto: después de la secundaria escribirás el tema del informe.'
          : null,
      }),
    );
    if (this.primary()?.id === p.id) return;

    this.primary.set(p);
    this.secondary.set(null);
    this.tertiary.set(null);
    this.tertiaryDetail.set(null);
    this.secondaries.set([]);
    this.tertiaries.set([]);
    this.values.set({});
    this.touch();

    this.loadingSecondaries.set(true);
    try {
      const list = await firstValueFrom(this.catalog.secondaryCategories(p.id));
      if (this.primary()?.id === p.id) this.secondaries.set(list);
    } catch (err) {
      this.catalogError.set(toApiError(err).message);
    } finally {
      this.loadingSecondaries.set(false);
    }
  }

  async selectSecondary(s: Category): Promise<void> {
    this.pinHelp(helpFromInfo(s, 'Categoría secundaria', '📂'));
    if (this.secondary()?.id === s.id) return;

    this.secondary.set(s);
    this.tertiary.set(null);
    this.tertiaryDetail.set(null);
    this.tertiaries.set([]);
    this.values.set({});
    this.touch();

    if (this.isInformes()) return;

    this.loadingTertiaries.set(true);
    try {
      const list = await firstValueFrom(this.catalog.tertiaryCategories(s.id));
      if (this.secondary()?.id === s.id) this.tertiaries.set(list);
    } catch (err) {
      this.catalogError.set(toApiError(err).message);
    } finally {
      this.loadingTertiaries.set(false);
    }
  }

  async selectTertiary(t: Category): Promise<void> {
    this.pinHelp(helpFromInfo(t, 'Tipo de proyecto', '🏗️'));
    if (this.tertiary()?.id === t.id) return;

    this.tertiary.set(t);
    this.tertiaryDetail.set(null);
    this.values.set({});
    this.touch();

    this.loadingDetail.set(true);
    try {
      const detail = await firstValueFrom(this.catalog.tertiaryDetail(t.id));
      if (this.tertiary()?.id !== t.id) return;
      this.tertiaryDetail.set(detail);
      this.values.set(Object.fromEntries(detail.parameters.map((p) => [p.id, null])));
    } catch (err) {
      this.catalogError.set(toApiError(err).message);
    } finally {
      this.loadingDetail.set(false);
    }
  }

  setTopicName(value: string): void {
    this.topicName.set(value);
    this.touch();
  }

  /* ============================================================
   |  Paso 2: título y datos
   * ============================================================ */
  setTitle(value: string): void {
    this.title.set(value);
    this.touch();
  }

  setValue(parameterId: number, value: ParameterValue): void {
    this.values.update((v) => ({ ...v, [parameterId]: value }));
    this.touch();
  }

  /**
   * Punto elegido en el mapa: guarda las coordenadas y copia a los parámetros direccionales
   * los datos que el mapa encontró (los que no encontró se dejan como estaban).
   */
  setMapLocation(lat: number, lng: number, fields: AddressFields): void {
    this.latitude.set(lat);
    this.longitude.set(lng);
    this.values.update((v) => {
      const next = { ...v };
      for (const p of this.addressParameters()) {
        const key = addressFieldOf(p.name);
        const found = key ? fields[key] : null;
        if (found) next[p.id] = found;
      }
      return next;
    });
    this.touch();
  }

  toggleStandard(s: Standard): void {
    this.standardIds.update((ids) => (ids.includes(s.id) ? ids.filter((x) => x !== s.id) : [...ids, s.id]));
    this.pinHelp({
      icon: '📐',
      eyebrow: 'Norma técnica',
      title: s.name,
      description: s.description || 'Esta norma todavía no tiene una descripción cargada.',
      tips: s.country ? [`País de la norma: ${s.country}`] : [],
      isItem: true,
    });
    this.touch();
  }

  removeStandard(id: number): void {
    this.standardIds.update((ids) => ids.filter((x) => x !== id));
    this.touch();
  }

  addOwner(owner: Owner): void {
    if (this.owners().some((o) => o.id === owner.id)) return;
    this.owners.update((list) => [...list, { ...owner, is_principal: list.length === 0 }]);
    this.touch();
  }

  removeOwner(id: number): void {
    this.owners.update((list) => ensurePrincipal(list.filter((o) => o.id !== id)));
    this.touch();
  }

  setPrincipalOwner(id: number): void {
    this.owners.update((list) => list.map((o) => ({ ...o, is_principal: o.id === id })));
    this.touch();
  }

  addProjectist(engineer: Engineer): void {
    if (this.projectists().some((e) => e.id === engineer.id)) return;
    this.projectists.update((list) => [...list, { ...engineer, is_principal: list.length === 0 }]);
    this.touch();
  }

  removeProjectist(id: number): void {
    this.projectists.update((list) => ensurePrincipal(list.filter((e) => e.id !== id)));
    this.touch();
  }

  setPrincipalProjectist(id: number): void {
    this.projectists.update((list) => list.map((e) => ({ ...e, is_principal: e.id === id })));
    this.touch();
  }

  setLocation(field: 'address' | 'zone' | 'municipality', value: string): void {
    this[field].set(value);
    this.touch();
  }

  /* ============================================================
   |  Al salir del paso 2: guardar borrador
   * ============================================================ */
  async save(): Promise<boolean> {
    this.saving.set(true);
    this.saveError.set(null);
    try {
      const current = this.procedure();
      const payload = this.buildPayload();
      const p = await firstValueFrom(
        current ? this.procedures.update(current.hash_code, payload) : this.procedures.create(payload),
      );
      this.procedure.set(p);
      this.dirty.set(false);
      this.rememberDraft(p);
      return true;
    } catch (err) {
      this.saveError.set(toApiError(err));
      return false;
    } finally {
      this.saving.set(false);
    }
  }

  /** Paso al que pertenece un campo con error, para ofrecer "Ir a corregir" */
  stepForField(field: string): StepId {
    return FIELD_STEP.find(([prefix]) => field.startsWith(prefix))?.[1] ?? 2;
  }

  private buildPayload(): ProcedurePayload {
    const v = this.values();
    const location = this.locationFromParameters() ?? {
      address: this.address().trim() || null,
      zone: this.zone().trim() || null,
      municipality: this.municipality().trim() || null,
    };

    return {
      title: this.title().trim(),
      primary_category_id: this.primary()!.id,
      secondary_category_id: this.secondary()!.id,
      tertiary_category_id: this.isInformes() ? null : this.tertiary()?.id ?? null,
      topic_name: this.isInformes() ? this.topicName().trim() : null,
      ...location,
      latitude: this.latitude(),
      longitude: this.longitude(),
      standards: this.standardIds(),
      owners: this.owners().map((o) => ({ owner_id: o.id, is_principal: o.is_principal })),
      projectists: this.projectists().map((e) => ({ engineer_id: e.id, is_principal: e.is_principal })),
      // Los vacíos no se envían (el backend exige solo los obligatorios)
      parameters: this.parameters()
        .filter((p) => !isEmptyValue(v[p.id]))
        .map((p) => ({ parameter_id: p.id, value: v[p.id] ?? null })),
    };
  }

  /** Si la terciaria tiene parámetros direccionales, la ubicación del trámite sale de ellos */
  private locationFromParameters(): Pick<ProcedurePayload, 'address' | 'zone' | 'municipality'> | null {
    const params = this.addressParameters();
    if (!params.length) return null;

    const v = this.values();
    const get = (field: AddressField) => {
      const p = params.find((x) => addressFieldOf(x.name) === field);
      const value = p ? v[p.id] : null;
      return typeof value === 'string' && value.trim() ? value.trim() : null;
    };

    return {
      address: get('direccion') ?? get('calle'),
      zone: get('zona'),
      municipality: get('municipio'),
    };
  }

  /* ============================================================
   |  Paso 3: documentos · Paso 4: vista previa y envío
   * ============================================================ */
  addUploadedFile(file: UploadedFileInfo): void {
    this.procedure.update((p) => {
      if (!p) return p;
      // Sin documento requerido = documento adicional del usuario
      if (file.required_document_id === null) {
        return { ...p, extra_documents: [...p.extra_documents, file] };
      }
      return {
        ...p,
        required_documents: p.required_documents.map((d) =>
          d.id === file.required_document_id ? { ...d, files: [...d.files, file] } : d,
        ),
      };
    });
  }

  removeUploadedFile(fileId: number): void {
    this.procedure.update((p) =>
      p
        ? {
            ...p,
            required_documents: p.required_documents.map((d) => ({ ...d, files: d.files.filter((f) => f.id !== fileId) })),
            extra_documents: p.extra_documents.filter((f) => f.id !== fileId),
          }
        : p,
    );
  }

  async submit(): Promise<boolean> {
    const current = this.procedure();
    if (!current) return false;

    this.submitting.set(true);
    this.submitError.set(null);
    try {
      this.procedure.set(await firstValueFrom(this.procedures.submit(current.hash_code)));
      this.forgetDraft();
      this.goTo(5);
      return true;
    } catch (err) {
      this.submitError.set(toApiError(err));
      return false;
    } finally {
      this.submitting.set(false);
    }
  }

  /* ============================================================
   |  Navegación y ayuda
   * ============================================================ */
  canVisit(step: StepId): boolean {
    if (this.resuming()) return false;
    if (this.isLocked()) return step === 5;

    const ok1 = this.step1Missing().length === 0;
    const ok2 = ok1 && this.step2Missing().length === 0;
    switch (step) {
      case 1:
        return true;
      case 2:
        return ok1;
      case 3:
        return ok2 && !!this.procedure() && !this.dirty();
      case 4:
        return ok2 && !!this.procedure() && !this.dirty() && this.missingDocuments().length === 0;
      case 5:
        return false;
    }
  }

  goTo(step: StepId): void {
    if (step !== 5 && !this.canVisit(step)) return;
    this.step.set(step);
    this.pinnedHelp.set(null);
    this.hoverHelp.set(null);
    this.saveError.set(null);
  }

  pinHelp(help: HelpContent): void {
    this.pinnedHelp.set(help);
    this.hoverHelp.set(null);
    this.helpTab.set('ayuda');
  }

  previewHelp(info: HelpInfo | null, eyebrow: string, icon: string): void {
    this.hoverHelp.set(info ? helpFromInfo(info, eyebrow, icon) : null);
  }

  /** Reinicia todo para un trámite nuevo */
  reset(): void {
    this.forgetDraft();
    this.procedure.set(null);
    this.title.set('');
    this.primary.set(null);
    this.secondary.set(null);
    this.tertiary.set(null);
    this.tertiaryDetail.set(null);
    this.secondaries.set([]);
    this.tertiaries.set([]);
    this.topicName.set('');
    this.values.set({});
    this.standardIds.set([]);
    this.owners.set([]);
    this.projectists.set([]);
    this.address.set('');
    this.zone.set('');
    this.municipality.set('');
    this.latitude.set(null);
    this.longitude.set(null);
    this.dirty.set(false);
    this.submitError.set(null);
    this.catalogError.set(null);
    this.goTo(1);
  }

  private touch(): void {
    if (this.procedure()) this.dirty.set(true);
  }

  /* ---------- Borrador recordado en el navegador ---------- */
  private rememberDraft(p: Procedure): void {
    const ref: DraftRef = { hash: p.hash_code, number: p.number, title: p.title };
    this.draftRef.set(ref);
    try {
      localStorage.setItem(DRAFT_KEY, JSON.stringify(ref));
    } catch {
      /* sin almacenamiento: el enlace con ?t= sigue funcionando */
    }
  }

  forgetDraft(): void {
    this.draftRef.set(null);
    try {
      localStorage.removeItem(DRAFT_KEY);
    } catch {
      /* nada que limpiar */
    }
  }
}

/* ============================================================
 |  Utilidades
 * ============================================================ */
function readDraft(): DraftRef | null {
  try {
    const raw = localStorage.getItem(DRAFT_KEY);
    return raw ? (JSON.parse(raw) as DraftRef) : null;
  } catch {
    return null;
  }
}

function ensurePrincipal<T extends { is_principal: boolean }>(list: T[]): T[] {
  if (!list.length || list.some((x) => x.is_principal)) return list;
  return list.map((x, i) => ({ ...x, is_principal: i === 0 }));
}

function principalFirst<T extends { is_principal: boolean }>(list: T[]): T[] {
  return [...list].sort((a, b) => Number(b.is_principal) - Number(a.is_principal));
}

/** Vacío = sin valor (false en un Sí/No sí cuenta como respuesta) */
export function isEmptyValue(value: ParameterValue | undefined): boolean {
  return value === null || value === undefined || (typeof value === 'string' && value.trim() === '');
}

export function formatNumber(value: number): string {
  // Hasta 6 decimales (precisión del backend): se muestra lo que el usuario escribió
  return new Intl.NumberFormat('es-BO', { maximumFractionDigits: 6 }).format(value);
}

/** Texto que se muestra en la carátula según el tipo de dato */
export function formatParameterValue(value: ParameterValue, dataType: ParameterDataType): string | null {
  if (isEmptyValue(value)) return null;
  switch (dataType) {
    case 'numero':
      return typeof value === 'number' ? formatNumber(value) : String(value);
    case 'booleano':
      return value ? 'Sí' : 'No';
    case 'fecha': {
      const [y, m, d] = String(value).split('-');
      return y && m && d ? `${d}/${m}/${y}` : String(value);
    }
    default:
      return String(value).trim().toUpperCase();
  }
}

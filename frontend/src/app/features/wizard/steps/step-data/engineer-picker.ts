import { Component, ElementRef, computed, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { Subject, catchError, debounceTime, of, switchMap, tap } from 'rxjs';

import { Engineer, EngineerFormOptions } from '../../../../core/models/procedure.models';
import { CatalogService } from '../../../../core/services/catalog.service';
import { ApiError, fieldError, toApiError } from '../../../../core/utils/api-error';
import { WizardStore } from '../../wizard.store';

interface EngineerForm {
  name: string;
  father_last_name: string;
  mother_last_name: string;
  rni: string;
  ci: string;
  branch_id: string;
  university_id: string;
  sib_departmental: string;
  phone: string;
  email: string;
  specialties: number[];
}

const EMPTY_FORM: EngineerForm = {
  name: '',
  father_last_name: '',
  mother_last_name: '',
  rni: '',
  ci: '',
  branch_id: '',
  university_id: '',
  sib_departmental: 'la_paz',
  phone: '',
  email: '',
  specialties: [],
};

/** Buscar ingenieros proyectistas (solo activos) y registrarlos al vuelo */
@Component({
  selector: 'app-engineer-picker',
  templateUrl: './engineer-picker.html',
  host: { '(document:click)': 'onDocumentClick($event)' },
})
export class EngineerPicker {
  protected readonly store = inject(WizardStore);
  private readonly catalog = inject(CatalogService);
  private readonly host = inject(ElementRef<HTMLElement>);

  protected readonly query = signal('');
  protected readonly results = signal<Engineer[]>([]);
  protected readonly searching = signal(false);
  protected readonly open = signal(false);

  protected readonly creating = signal(false);
  protected readonly options = signal<EngineerFormOptions | null>(null);
  protected readonly form = signal<EngineerForm>({ ...EMPTY_FORM });
  protected readonly saving = signal(false);
  protected readonly error = signal<ApiError | null>(null);
  protected readonly fieldError = fieldError;

  /** Buscador de especialidades del formulario de alta */
  protected readonly specialtyQuery = signal('');
  protected readonly filteredSpecialties = computed(() => {
    const term = normalize(this.specialtyQuery().trim());
    const all = this.options()?.specialties ?? [];
    return term ? all.filter((s) => normalize(s.name).includes(term)) : all;
  });

  /** Especialidades sugeridas para el tipo de proyecto elegido */
  protected readonly recommended = computed(() => this.store.tertiaryDetail()?.specialties.map((s) => s.name) ?? []);

  /** El RNI ya agregado no se vuelve a ofrecer */
  protected readonly alreadyAdded = computed(() => {
    const rni = this.query().trim();
    return !!rni && this.store.projectists().some((e) => e.rni === rni);
  });

  protected readonly available = computed(() => {
    const added = new Set(this.store.projectists().map((e) => e.id));
    const rni = this.query().trim();
    return this.results().filter((e) => e.rni === rni && !added.has(e.id));
  });

  private readonly search$ = new Subject<string>();

  constructor() {
    // Solo se busca por RNI completo y exacto
    this.search$
      .pipe(
        debounceTime(400),
        tap((rni) => this.searching.set(!!rni)),
        switchMap((rni) =>
          rni ? this.catalog.findEngineerByRni(rni).pipe(catchError(() => of<Engineer[]>([]))) : of<Engineer[]>([]),
        ),
        takeUntilDestroyed(),
      )
      .subscribe((list) => {
        this.results.set(list);
        this.searching.set(false);
      });
  }

  protected matches(engineer: Engineer): boolean {
    const rec = this.recommended();
    return rec.length > 0 && engineer.specialties.some((s) => rec.includes(s));
  }

  protected onInput(event: Event): void {
    const term = (event.target as HTMLInputElement).value;
    this.query.set(term);
    this.results.set([]);
    this.open.set(true);
    this.search$.next(term.trim());
  }

  protected onFocus(): void {
    this.open.set(true);
    this.search$.next(this.query().trim());
    const rec = this.recommended();
    this.store.pinHelp({
      icon: '👷',
      eyebrow: 'Proyectistas',
      title: '¿Qué ingeniero agrego?',
      description:
        'El ingeniero proyectista es quien diseñó el proyecto. Su nombre y RNI aparecen bajo el espacio de sello y firma de la carátula.',
      tips: [
        'Escribe el número de RNI completo del ingeniero: solo aparece si coincide exactamente.',
        'Puedes agregar varios ingenieros; cada uno tendrá su espacio de firma.',
        'Marca con ★ al proyectista principal.',
        'Si el ingeniero no está registrado, usa "+ Nuevo ingeniero".',
      ],
      warning: rec.length
        ? `Para este tipo de proyecto se recomienda un ingeniero con especialidad en: ${rec.join(', ')}.`
        : 'Solo aparecen ingenieros activos. Los suspendidos no pueden firmar.',
      isItem: true,
    });
  }

  protected pickFirst(): void {
    const first = this.available()[0];
    if (first) this.pick(first);
  }

  protected pick(engineer: Engineer): void {
    this.store.addProjectist(engineer);
    this.query.set('');
    this.open.set(false);
  }

  protected startCreate(): void {
    this.form.set({ ...EMPTY_FORM, rni: this.query().trim() });
    this.specialtyQuery.set('');
    this.error.set(null);
    this.creating.set(true);
    this.open.set(false);

    if (!this.options()) {
      this.catalog.engineerFormOptions().subscribe({
        next: (o) => this.options.set(o),
        error: (err) => this.error.set(toApiError(err)),
      });
    }
  }

  protected set(field: Exclude<keyof EngineerForm, 'specialties'>, event: Event): void {
    const value = (event.target as HTMLInputElement | HTMLSelectElement).value;
    this.form.update((f) => ({ ...f, [field]: value }));
  }

  protected toggleSpecialty(id: number): void {
    this.form.update((f) => ({
      ...f,
      specialties: f.specialties.includes(id) ? f.specialties.filter((x) => x !== id) : [...f.specialties, id],
    }));
  }

  protected hasFieldErrors(error: ApiError): boolean {
    return Object.keys(error.errors).length > 0;
  }

  protected canSave(): boolean {
    const f = this.form();
    return !!(f.name.trim() && f.father_last_name.trim() && f.rni.trim() && f.branch_id && f.university_id);
  }

  protected save(): void {
    const f = this.form();
    this.saving.set(true);
    this.error.set(null);
    this.catalog
      .createEngineer({
        name: f.name,
        father_last_name: f.father_last_name,
        mother_last_name: f.mother_last_name || null,
        rni: f.rni,
        ci: f.ci.trim().toUpperCase() || null,
        branch_id: Number(f.branch_id),
        university_id: Number(f.university_id),
        sib_departmental: f.sib_departmental,
        phone: f.phone || null,
        email: f.email || null,
        specialties: f.specialties,
      })
      .subscribe({
        next: (engineer) => {
          this.store.addProjectist(engineer);
          this.creating.set(false);
          this.query.set('');
          this.saving.set(false);
        },
        error: (err) => {
          this.error.set(toApiError(err));
          this.saving.set(false);
        },
      });
  }

  protected initials(name: string): string {
    return name
      .split(' ')
      .filter(Boolean)
      .slice(0, 2)
      .map((w) => w[0])
      .join('')
      .toUpperCase();
  }

  protected onDocumentClick(event: MouseEvent): void {
    if (this.open() && !this.host.nativeElement.contains(event.target as Node)) {
      this.open.set(false);
    }
  }
}

/** Minúsculas y sin tildes, para que "electrica" encuentre "Eléctrica" */
function normalize(text: string): string {
  return text.normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase();
}

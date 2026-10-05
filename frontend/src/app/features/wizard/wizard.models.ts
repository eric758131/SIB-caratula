import { Engineer, Owner } from '../../core/models/procedure.models';

export type StepId = 1 | 2 | 3 | 4 | 5;

export const STEPS: { id: StepId; label: string }[] = [
  { id: 1, label: 'Categoría' },
  { id: 2, label: 'Datos' },
  { id: 3, label: 'Documentos' },
  { id: 4, label: 'Vista Previa' },
  { id: 5, label: 'Descargar' },
];

export interface OwnerItem extends Owner {
  is_principal: boolean;
}

export interface EngineerItem extends Engineer {
  is_principal: boolean;
}

/** Borrador recordado en el navegador para retomarlo */
export interface DraftRef {
  hash: string;
  number: string;
  title: string;
}

/** Lo que muestra el panel de ayuda de la derecha */
export interface HelpContent {
  icon: string;
  eyebrow: string;
  title: string;
  description?: string | null;
  example?: string | null;
  importantNotes?: string | null;
  /** Imagen tras "¿Qué es?", tras el ejemplo y tras lo importante (null = no se muestra) */
  image1?: string | null;
  image2?: string | null;
  image3?: string | null;
  /** Documento de definición que el usuario puede abrir (null = no se muestra) */
  definition?: string | null;
  tips?: string[];
  warning?: string | null;
  /** true = ayuda de un elemento concreto (muestra "volver a la guía del paso") */
  isItem?: boolean;
}

/* ============================================================
 |  Parámetros direccionales (se llenan desde el mapa)
 * ============================================================ */
export type AddressField = 'calle' | 'direccion' | 'zona' | 'municipio';

/** Lo que el mapa encontró para el punto elegido (null = no lo encontró) */
export type AddressFields = Record<AddressField, string | null>;

/** A qué dato del mapa corresponde un parámetro direccional, por su nombre */
export function addressFieldOf(parameterName: string): AddressField | null {
  const name = parameterName
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .toLowerCase();
  if (name.includes('calle') || name.includes('avenida')) return 'calle';
  if (name.includes('direccion')) return 'direccion';
  if (name.includes('zona') || name.includes('barrio')) return 'zona';
  if (name.includes('municipio')) return 'municipio';
  return null;
}

/** Formatos y tamaño aceptados por el backend (ProcedureApiController / config/media-library.php) */
export const UPLOAD_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'dwg', 'dxf', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
export const UPLOAD_MAX_MB = 40;

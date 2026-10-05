/* ============================================================
 |  Catálogos (respuestas de CatalogController)
 * ============================================================ */

/** Ayudas visuales: comunes a títulos, categorías y parámetros */
export interface HelpInfo {
  id: number;
  name: string;
  description: string | null;
  example: string | null;
  important_notes: string | null;
  /** Miniatura en las listas de opciones (solo el logo de las primarias) */
  images: string[];
  /** Imágenes de la ayuda: 1 tras "¿Qué es?", 2 tras el ejemplo, 3 tras lo importante */
  image_1?: string | null;
  image_2?: string | null;
  image_3?: string | null;
  /** Documento de definición (PDF o imagen): solo lo tienen las secundarias */
  definition?: string | null;
}

export interface Category extends HelpInfo {
  code: string | null;
}

export interface PrimaryCategory extends Category {
  logo: string | null;
  /** true = INFORMES: no tiene terciaria, se escribe un topic */
  is_special: boolean;
}

/** Clasificación funcional: "direccional" = calle, dirección, zona o municipio (se llenan desde el mapa) */
export type ParameterType = 'parametrico' | 'caratula' | 'judicial' | 'direccional';

/** Tipo de dato que el usuario ingresa */
export type ParameterDataType = 'numero' | 'texto' | 'fecha' | 'booleano';

/** Valor de un parámetro: número, texto, fecha (AAAA-MM-DD) o Sí/No */
export type ParameterValue = number | string | boolean | null;

export interface TertiaryParameter {
  id: number;
  name: string;
  description: string | null;
  unit_of_measure: string | null;
  parameter_type: ParameterType;
  data_type: ParameterDataType;
  /** Solo números: decimales permitidos (0 = entero, null = sin límite) */
  max_decimals?: number | null;
  /** null = no cotiza */
  tariff_index: number | null;
  is_required: boolean;
}

/** Documento que el usuario debe entregar: lo descarga en el paso 1 y lo sube lleno en el 3 */
export interface RequiredDocument {
  id: number;
  name: string;
  /** Formato descargable (null si el documento no tiene archivo: lo prepara el usuario) */
  download_url: string | null;
}

export interface TertiaryDetail extends Category {
  parameters: TertiaryParameter[];
  required_documents: RequiredDocument[];
  /** Todos los formatos en un .zip (null si ningún documento tiene archivo) */
  required_documents_zip_url: string | null;
  specialties: IdName[];
}

export interface IdName {
  id: number;
  name: string;
}

export type Topic = IdName;

export interface Standard {
  id: number;
  name: string;
  description: string | null;
  country: string | null;
}

export interface Owner {
  id: number;
  name: string;
  phone: string | null;
}

export interface Engineer {
  id: number;
  full_name: string;
  rni: string;
  ci: string;
  specialties: string[];
}

export interface EngineerFormOptions {
  branches: IdName[];
  universities: IdName[];
  specialties: IdName[];
  sib_departmentals: { value: string; label: string }[];
}

/* ============================================================
 |  Alta rápida
 * ============================================================ */
export interface OwnerCreate {
  name: string;
  phone?: string | null;
}

export interface EngineerCreate {
  branch_id: number;
  university_id: number;
  rni: string;
  name: string;
  father_last_name: string;
  mother_last_name?: string | null;
  ci: string;
  phone?: string | null;
  email?: string | null;
  sib_departmental: string;
  specialties?: number[];
}

/* ============================================================
 |  Trámite (ProcedureApiController)
 * ============================================================ */

/** Lo que se envía en POST /procedures y PUT /procedures/{hash} */
export interface ProcedurePayload {
  /** Se guarda tal cual en procedures.title (en mayúsculas) */
  title: string;

  primary_category_id: number;
  secondary_category_id: number;
  /** null cuando la primaria es INFORMES */
  tertiary_category_id?: number | null;
  /** Solo para INFORMES: texto libre que se guarda en topics */
  topic_name?: string | null;

  address?: string | null;
  zone?: string | null;
  municipality?: string | null;
  latitude?: number | null;
  longitude?: number | null;
  observations?: string | null;

  standards?: number[];
  owners: { owner_id: number; is_principal?: boolean }[];
  projectists: { engineer_id: number; is_principal?: boolean }[];
  parameters?: { parameter_id: number; value: ParameterValue }[];
}

export type ProcedureStatus =
  | 'pendiente' | 'registrado' | 'cotizado' | 'designado' | 'en_verificacion'
  | 'observado' | 'en_correccion' | 'corregido' | 'anulado' | 'aprobado'
  | 'por_pagar' | 'pagado' | 'por_visar' | 'visado' | 'notificado'
  | 'en_recepcion' | 'entregado';

/** Archivo que subió el usuario (tabla documents) */
export interface UploadedFileInfo {
  id: number;
  /** Documento requerido al que responde; null = documento adicional */
  required_document_id: number | null;
  file_name: string;
  mime_type: string | null;
  size: number;
  human_size: string;
  download_url: string;
}

export interface ProcedureRequiredDocument extends RequiredDocument {
  /** Archivos ya subidos para este documento (puede haber varios) */
  files: UploadedFileInfo[];
}

export interface ProcedureParameter {
  parameter_id: number;
  name: string;
  parameter_type: ParameterType;
  data_type: ParameterDataType;
  unit_of_measure: string | null;
  value: ParameterValue;
  tariff_index: number | null;
  subtotal: number | null;
}

export interface Procedure {
  hash_code: string;
  number: string;
  title: string;
  entry_date: string;
  status: ProcedureStatus;
  procedure_type: string;
  /** true mientras está "pendiente": se puede editar y subir documentos */
  is_draft: boolean;
  address: string | null;
  zone: string | null;
  municipality: string | null;
  latitude: string | null;
  longitude: string | null;
  observations: string | null;
  total_quote_amount: number;

  primary_category: { id: number; name: string; logo: string | null; is_special: boolean };
  secondary_category: IdName;
  tertiary_category: { id: number; code: string | null; name: string } | null;
  topic: Topic | null;

  standards: { id: number; name: string; country: string | null }[];
  /** Ordenados: el principal primero */
  owners: (Owner & { is_principal: boolean })[];
  projectists: { id: number; full_name: string; rni: string; ci: string; is_principal: boolean }[];
  parameters: ProcedureParameter[];
  required_documents: ProcedureRequiredDocument[];
  /** Documentos que el usuario envía por su cuenta, además de los requeridos */
  extra_documents: UploadedFileInfo[];
}

/** Eventos de una subida con progreso */
export type UploadEvent =
  | { type: 'progress'; percent: number }
  | { type: 'done'; file: UploadedFileInfo };

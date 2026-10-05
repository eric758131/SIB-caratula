import { HttpErrorResponse } from '@angular/common/http';

/** Error normalizado de la API de Laravel */
export interface ApiError {
  status: number;
  /** Mensaje general para mostrar en un aviso */
  message: string;
  /** Errores por campo (422). Claves como 'owners', 'parameters.0.quantity' */
  errors: Record<string, string[]>;
}

export function toApiError(err: unknown): ApiError {
  if (!(err instanceof HttpErrorResponse)) {
    return { status: 0, message: 'Ocurrió un error inesperado.', errors: {} };
  }

  switch (err.status) {
    case 0:
      return { status: 0, message: 'No se pudo conectar con el servidor.', errors: {} };
    case 413:
      return { status: 413, message: 'El archivo es demasiado grande para el servidor.', errors: {} };
  }

  const body = err.error ?? {};
  return {
    status: err.status,
    message: body.message ?? 'Ocurrió un error en el servidor.',
    errors: body.errors ?? {},
  };
}

/** Primer error de un campo (útil en formularios reactivos) */
export function fieldError(error: ApiError | null, field: string): string | null {
  return error?.errors[field]?.[0] ?? null;
}

import { HttpClient, HttpEvent, HttpEventType } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, filter, map } from 'rxjs';

import { environment } from '../../../environments/environment';
import { Procedure, ProcedurePayload, UploadEvent, UploadedFileInfo } from '../models/procedure.models';

/**
 * Trámites. Flujo del asistente:
 *  create() → borrador "pendiente" (guardar el hash_code)
 *  update() → si el usuario vuelve a pasos anteriores
 *  uploadDocument() → un archivo por llamada, con progreso
 *  submit() → valida que estén todos los documentos y pasa a "registrado"
 *  get() → carátula + documentos descargables de la terciaria
 */
@Injectable({ providedIn: 'root' })
export class ProcedureService {
  private readonly http = inject(HttpClient);
  private readonly api = `${environment.apiUrl}/procedures`;

  create(payload: ProcedurePayload): Observable<Procedure> {
    return this.http.post<Procedure>(this.api, payload);
  }

  update(hash: string, payload: ProcedurePayload): Observable<Procedure> {
    return this.http.put<Procedure>(`${this.api}/${hash}`, payload);
  }

  get(hash: string): Observable<Procedure> {
    return this.http.get<Procedure>(`${this.api}/${hash}`);
  }

  submit(hash: string): Observable<Procedure> {
    return this.http.post<Procedure>(`${this.api}/${hash}/submit`, {});
  }

  /**
   * Sube un archivo y emite el progreso (0-100).
   * requiredDocumentId = documento requerido al que responde; null = documento adicional.
   * Para varios archivos, llamar una vez por archivo (p. ej. con concatMap).
   */
  uploadDocument(hash: string, requiredDocumentId: number | null, file: File): Observable<UploadEvent> {
    const form = new FormData();
    if (requiredDocumentId !== null) form.append('required_document_id', String(requiredDocumentId));
    form.append('file', file, file.name);

    return this.http
      .post<UploadedFileInfo>(`${this.api}/${hash}/documents`, form, {
        reportProgress: true,
        observe: 'events',
      })
      .pipe(
        map((event: HttpEvent<UploadedFileInfo>): UploadEvent | null => {
          if (event.type === HttpEventType.UploadProgress) {
            const percent = event.total ? Math.round((100 * event.loaded) / event.total) : 0;
            return { type: 'progress', percent };
          }
          if (event.type === HttpEventType.Response && event.body) {
            return { type: 'done', file: event.body };
          }
          return null;
        }),
        filter((e): e is UploadEvent => e !== null),
      );
  }

  deleteDocument(hash: string, fileId: number): Observable<void> {
    return this.http.delete<void>(`${this.api}/${hash}/documents/${fileId}`);
  }
}

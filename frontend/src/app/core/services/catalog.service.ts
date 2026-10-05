import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';

import { environment } from '../../../environments/environment';
import {
  Category,
  Engineer,
  EngineerCreate,
  EngineerFormOptions,
  Owner,
  OwnerCreate,
  PrimaryCategory,
  Standard,
  TertiaryDetail,
  Topic,
} from '../models/procedure.models';

/**
 * Catálogos del asistente + búsqueda y alta rápida de propietarios e ingenieros.
 */
@Injectable({ providedIn: 'root' })
export class CatalogService {
  private readonly http = inject(HttpClient);
  private readonly api = environment.apiUrl;

  /** En la lista, las primarias muestran su logo como miniatura; image_1/2/3 van al panel de ayuda */
  primaryCategories(): Observable<PrimaryCategory[]> {
    return this.http
      .get<PrimaryCategory[]>(`${this.api}/primary-categories`)
      .pipe(map((list) => list.map((c) => ({ ...c, images: c.logo ? [c.logo] : [] }))));
  }

  /** Secundarias y terciarias no llevan miniatura en la lista (sus image_1/2/3 sí se ven en la ayuda) */
  secondaryCategories(primaryId: number): Observable<Category[]> {
    return this.http
      .get<Category[]>(`${this.api}/primary-categories/${primaryId}/secondaries`)
      .pipe(map(withoutImages));
  }

  tertiaryCategories(secondaryId: number): Observable<Category[]> {
    return this.http
      .get<Category[]>(`${this.api}/secondaries/${secondaryId}/tertiaries`)
      .pipe(map(withoutImages));
  }

  /** Parámetros (con ayudas visuales), documentos requeridos y especialidades */
  tertiaryDetail(tertiaryId: number): Observable<TertiaryDetail> {
    return this.http.get<TertiaryDetail>(`${this.api}/tertiaries/${tertiaryId}`);
  }

  /** Sugerencias para el campo libre de INFORMES */
  topics(search = ''): Observable<Topic[]> {
    return this.http.get<Topic[]>(`${this.api}/topics`, { params: this.searchParams(search) });
  }

  standards(): Observable<Standard[]> {
    return this.http.get<Standard[]>(`${this.api}/standards`);
  }

  /* ---------- Propietarios ---------- */
  /** Si ya existe un propietario con ese nombre, devuelve ese mismo registro (existing: true) */
  createOwner(data: OwnerCreate): Observable<Owner & { existing: boolean }> {
    return this.http.post<Owner & { existing: boolean }>(`${this.api}/owners`, data);
  }

  /* ---------- Ingenieros ---------- */
  /** Solo devuelve al ingeniero activo con ese RNI exacto */
  findEngineerByRni(rni: string): Observable<Engineer[]> {
    return this.http.get<Engineer[]>(`${this.api}/engineers`, { params: this.searchParams(rni) });
  }

  engineerFormOptions(): Observable<EngineerFormOptions> {
    return this.http.get<EngineerFormOptions>(`${this.api}/engineers/form-options`);
  }

  createEngineer(data: EngineerCreate): Observable<Engineer> {
    return this.http.post<Engineer>(`${this.api}/engineers`, data);
  }

  private searchParams(search: string): HttpParams {
    const term = search.trim();
    return term ? new HttpParams().set('search', term) : new HttpParams();
  }
}

function withoutImages(list: Category[]): Category[] {
  return list.map((c) => ({ ...c, images: [] }));
}

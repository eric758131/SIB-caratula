import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';

import { AddressFields } from '../../features/wizard/wizard.models';

/** Dirección de OpenStreetMap (solo los campos que usamos) */
interface NominatimAddress {
  road?: string;
  pedestrian?: string;
  footway?: string;
  residential?: string;
  house_number?: string;
  suburb?: string;
  neighbourhood?: string;
  quarter?: string;
  city_district?: string;
  hamlet?: string;
  city?: string;
  town?: string;
  village?: string;
  municipality?: string;
  county?: string;
}

interface NominatimPlace {
  lat: string;
  lon: string;
  display_name: string;
  address?: NominatimAddress;
}

export interface GeoPlace {
  lat: number;
  lng: number;
  label: string;
  fields: AddressFields;
}

/**
 * Búsqueda de direcciones y dirección de un punto con Nominatim (OpenStreetMap).
 * Gratis y sin API key; su política de uso pide como máximo 1 consulta por segundo,
 * por eso solo se consulta al buscar o al elegir un punto (nunca mientras se escribe).
 */
@Injectable({ providedIn: 'root' })
export class GeocodingService {
  private readonly http = inject(HttpClient);
  private readonly api = 'https://nominatim.openstreetmap.org';

  /** Busca direcciones en Bolivia */
  search(query: string): Observable<GeoPlace[]> {
    const params = new HttpParams()
      .set('q', query)
      .set('format', 'jsonv2')
      .set('addressdetails', '1')
      .set('countrycodes', 'bo')
      .set('accept-language', 'es')
      .set('limit', '5');

    return this.http
      .get<NominatimPlace[]>(`${this.api}/search`, { params })
      .pipe(map((list) => list.map((p) => toPlace(p))));
  }

  /** Dirección del punto elegido en el mapa */
  reverse(lat: number, lng: number): Observable<GeoPlace> {
    const params = new HttpParams()
      .set('lat', lat)
      .set('lon', lng)
      .set('format', 'jsonv2')
      .set('addressdetails', '1')
      .set('accept-language', 'es')
      .set('zoom', '18');

    return this.http
      .get<NominatimPlace>(`${this.api}/reverse`, { params })
      .pipe(map((p) => toPlace(p, lat, lng)));
  }
}

function toPlace(place: NominatimPlace, lat?: number, lng?: number): GeoPlace {
  const a = place.address ?? {};
  const street = a.road ?? a.pedestrian ?? a.footway ?? a.residential ?? null;

  return {
    lat: lat ?? Number(place.lat),
    lng: lng ?? Number(place.lon),
    label: place.display_name ?? '',
    fields: {
      calle: street,
      direccion: street
        ? a.house_number ? `${street} N° ${a.house_number}` : street
        : firstParts(place.display_name, 2),
      // En La Paz "quarter" es la zona conocida (Sopocachi) y "suburb" el macrodistrito (Cotahuma)
      zona: a.quarter ?? a.neighbourhood ?? a.suburb ?? a.city_district ?? a.hamlet ?? null,
      // "Municipio Nuestra Señora de La Paz" → "Nuestra Señora de La Paz"
      municipio: stripPrefix(a.city ?? a.town ?? a.village ?? a.municipality ?? a.county ?? null, 'Municipio '),
    },
  };
}

function stripPrefix(text: string | null, prefix: string): string | null {
  return text && text.startsWith(prefix) ? text.slice(prefix.length) : text;
}

function firstParts(text: string | undefined, count: number): string | null {
  const parts = (text ?? '').split(',').map((s) => s.trim()).filter(Boolean);
  return parts.length ? parts.slice(0, count).join(', ') : null;
}

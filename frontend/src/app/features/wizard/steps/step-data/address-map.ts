import { Component, DestroyRef, ElementRef, afterNextRender, computed, inject, signal, viewChild } from '@angular/core';
import * as L from 'leaflet';
import { Subscription } from 'rxjs';

import { GeoPlace, GeocodingService } from '../../../../core/services/geocoding.service';
import { WizardStore } from '../../wizard.store';
import { AddressField, addressFieldOf } from '../../wizard.models';

/** Centro inicial: La Paz */
const DEFAULT_CENTER: L.LatLngTuple = [-16.4955, -68.1336];

const FIELD_LABEL: Record<AddressField, string> = {
  calle: 'calle',
  direccion: 'dirección',
  zona: 'zona',
  municipio: 'municipio',
};

/**
 * Mapa (OpenStreetMap + Leaflet) para ubicar el proyecto.
 * Al elegir un punto se buscan su calle, dirección, zona y municipio
 * y se copian en los parámetros direccionales que coinciden.
 */
@Component({
  selector: 'app-address-map',
  templateUrl: './address-map.html',
})
export class AddressMap {
  protected readonly store = inject(WizardStore);
  private readonly geo = inject(GeocodingService);

  private readonly canvas = viewChild.required<ElementRef<HTMLElement>>('canvas');
  private map: L.Map | null = null;
  private marker: L.Marker | null = null;
  private lookup: Subscription | null = null;

  protected readonly query = signal('');
  protected readonly results = signal<GeoPlace[] | null>(null);
  protected readonly searching = signal(false);
  protected readonly locating = signal(false);
  /** Mensaje después de elegir un punto */
  protected readonly status = signal<{ kind: 'ok' | 'warn' | 'error' | 'busy'; text: string } | null>(null);

  /** Qué datos del mapa se pueden copiar (según los parámetros direccionales de la terciaria) */
  private readonly targets = computed(() =>
    this.store
      .addressParameters()
      .map((p) => addressFieldOf(p.name))
      .filter((f): f is AddressField => !!f),
  );

  constructor() {
    afterNextRender(() => this.initMap());
    inject(DestroyRef).onDestroy(() => {
      this.lookup?.unsubscribe();
      this.map?.remove();
    });
  }

  private initMap(): void {
    const lat = this.store.latitude();
    const lng = this.store.longitude();
    const hasPoint = lat !== null && lng !== null;

    this.map = L.map(this.canvas().nativeElement, {
      center: hasPoint ? [lat, lng] : DEFAULT_CENTER,
      zoom: hasPoint ? 17 : 13,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>',
    }).addTo(this.map);

    this.map.on('click', (e: L.LeafletMouseEvent) => this.pick(e.latlng.lat, e.latlng.lng));

    if (hasPoint) this.placeMarker(lat, lng);

    // El contenedor puede terminar de medirse después del primer render
    setTimeout(() => this.map?.invalidateSize(), 0);
  }

  private placeMarker(lat: number, lng: number): void {
    if (!this.map) return;
    if (this.marker) {
      this.marker.setLatLng([lat, lng]);
      return;
    }
    this.marker = L.marker([lat, lng], {
      draggable: true,
      keyboard: false,
      icon: L.divIcon({
        className: '',
        html: '<div class="address-map__pin" aria-hidden="true">📍</div>',
        iconSize: [34, 34],
        iconAnchor: [17, 32],
      }),
    }).addTo(this.map);
    this.marker.on('dragend', () => {
      const pos = this.marker!.getLatLng();
      this.pick(pos.lat, pos.lng);
    });
  }

  /** Punto elegido: se ubica el pin y se busca su dirección */
  private pick(lat: number, lng: number): void {
    this.placeMarker(lat, lng);
    this.results.set(null);
    this.status.set({ kind: 'busy', text: 'Buscando la dirección de ese punto…' });

    this.lookup?.unsubscribe();
    this.lookup = this.geo.reverse(lat, lng).subscribe({
      next: (place) => this.apply(place),
      error: () => {
        // Sin dirección igual se guarda el punto; los campos se escriben a mano
        this.store.setMapLocation(lat, lng, { calle: null, direccion: null, zona: null, municipio: null });
        this.status.set({
          kind: 'error',
          text: 'Guardamos el punto, pero no pudimos consultar su dirección. Escribe los datos abajo.',
        });
      },
    });
  }

  private apply(place: GeoPlace): void {
    this.store.setMapLocation(place.lat, place.lng, place.fields);

    const copied = this.targets().filter((f) => !!place.fields[f]);
    const missing = this.targets().filter((f) => !place.fields[f]);

    if (!copied.length) {
      this.status.set({ kind: 'warn', text: 'El mapa no tiene datos de dirección para ese punto. Escríbelos abajo.' });
    } else if (missing.length) {
      this.status.set({
        kind: 'warn',
        text: `Copiamos ${copied.map((f) => FIELD_LABEL[f]).join(', ')}. Completa a mano: ${missing.map((f) => FIELD_LABEL[f]).join(', ')}.`,
      });
    } else {
      this.status.set({ kind: 'ok', text: 'Copiamos la dirección en los campos de abajo. Revísalos y corrígelos si hace falta.' });
    }
  }

  protected search(): void {
    const q = this.query().trim();
    if (q.length < 3 || this.searching()) return;

    this.searching.set(true);
    this.geo.search(q).subscribe({
      next: (list) => {
        this.results.set(list);
        this.searching.set(false);
      },
      error: () => {
        this.results.set([]);
        this.searching.set(false);
      },
    });
  }

  protected choose(place: GeoPlace): void {
    this.results.set(null);
    this.map?.setView([place.lat, place.lng], 17);
    this.placeMarker(place.lat, place.lng);
    // El resultado de la búsqueda ya trae la dirección: no hace falta otra consulta
    this.apply(place);
  }

  protected useMyLocation(): void {
    if (!navigator.geolocation) {
      this.status.set({ kind: 'error', text: 'Tu navegador no permite usar la ubicación. Busca la dirección o toca el mapa.' });
      return;
    }
    this.locating.set(true);
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        this.locating.set(false);
        const { latitude, longitude } = pos.coords;
        this.map?.setView([latitude, longitude], 17);
        this.pick(latitude, longitude);
      },
      () => {
        this.locating.set(false);
        this.status.set({ kind: 'error', text: 'No pudimos obtener tu ubicación. Busca la dirección o toca el mapa.' });
      },
      { enableHighAccuracy: true, timeout: 10000 },
    );
  }

  protected onQuery(event: Event): void {
    this.query.set((event.target as HTMLInputElement).value);
  }

  protected showHelp(): void {
    this.store.pinHelp({
      icon: '🗺️',
      eyebrow: 'Ubicación',
      title: '¿Cómo marco la ubicación?',
      description: 'Marca en el mapa dónde está el proyecto. La calle, zona y municipio se copian solos en los campos.',
      tips: [
        'Escribe la dirección en el buscador y elige un resultado, o',
        'toca directamente el lugar en el mapa. Puedes arrastrar el 📍 para ajustarlo.',
        'Acerca el mapa con la rueda del mouse o con dos dedos.',
        'Revisa los campos de abajo: si algo no coincide, corrígelo a mano.',
      ],
      isItem: true,
    });
  }
}

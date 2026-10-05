import { Injectable, effect, signal } from '@angular/core';

export type ThemeId = 'claro' | 'oscuro' | 'verde';
export type FontSize = 'normal' | 'grande';

export interface ThemeOption {
  id: ThemeId;
  name: string;
  description: string;
  swatch: string[];
}

export const THEMES: ThemeOption[] = [
  { id: 'claro', name: 'Claro', description: 'Fondo claro con acentos verdes', swatch: ['#f4faf6', '#16a34a', '#0f2419'] },
  { id: 'oscuro', name: 'Oscuro', description: 'Descansa la vista con poca luz', swatch: ['#07120c', '#22c55e', '#e6f4ea'] },
  { id: 'verde', name: 'Verde SIB', description: 'Colores institucionales intensos', swatch: ['#052e16', '#15803d', '#bbf7d0'] },
];

const THEME_KEY = 'sib.theme';
const FONT_KEY = 'sib.font';

/**
 * Tema y tamaño de letra. Se aplican como data-theme / data-font en <html>
 * y se recuerdan en el navegador.
 */
@Injectable({ providedIn: 'root' })
export class ThemeService {
  readonly theme = signal<ThemeId>(read(THEME_KEY, ['claro', 'oscuro', 'verde'], 'claro'));
  readonly fontSize = signal<FontSize>(read(FONT_KEY, ['normal', 'grande'], 'normal'));

  constructor() {
    effect(() => {
      document.documentElement.dataset['theme'] = this.theme();
      write(THEME_KEY, this.theme());
    });
    effect(() => {
      document.documentElement.dataset['font'] = this.fontSize();
      write(FONT_KEY, this.fontSize());
    });
  }
}

function read<T extends string>(key: string, allowed: T[], fallback: T): T {
  try {
    const value = localStorage.getItem(key) as T | null;
    return value && allowed.includes(value) ? value : fallback;
  } catch {
    return fallback;
  }
}

function write(key: string, value: string): void {
  try {
    localStorage.setItem(key, value);
  } catch {
    /* almacenamiento bloqueado: solo no se recuerda */
  }
}

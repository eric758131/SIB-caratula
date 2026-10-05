import { Component, computed, inject, input, signal } from '@angular/core';

import { TertiaryParameter } from '../../../../core/models/procedure.models';
import { WizardStore, formatNumber, isEmptyValue } from '../../wizard.store';

const DATA_TYPE_TIP: Record<TertiaryParameter['data_type'], string> = {
  numero: 'Para los decimales puedes usar coma o punto (ej.: 120,5 o 120.5).',
  texto: 'Escríbelo tal como debe aparecer en la carátula.',
  fecha: 'Elige la fecha en el calendario.',
  booleano: 'Responde con Sí o No.',
};

/** Un parámetro del proyecto: el campo cambia según su tipo de dato */
@Component({
  selector: 'app-param-input',
  templateUrl: './param-input.html',
})
export class ParamInput {
  readonly param = input.required<TertiaryParameter>();

  protected readonly store = inject(WizardStore);
  protected readonly focused = signal(false);
  /** Números: lo escrito tal cual (acepta coma o punto) mientras se edita */
  private readonly raw = signal<string | null>(null);

  protected readonly value = computed(() => this.store.values()[this.param().id] ?? null);
  protected readonly isEmpty = computed(() => isEmptyValue(this.value()));
  protected readonly formatNumber = formatNumber;

  /** Lo que muestra el campo de número: lo escrito, o el valor guardado con coma decimal */
  protected readonly numberText = computed(() => {
    const raw = this.raw();
    if (raw !== null) return raw;
    const v = this.value();
    return typeof v === 'number' ? String(v).replace('.', ',') : '';
  });

  /** true si lo escrito no se pudo entender como número */
  protected readonly isInvalid = computed(() => {
    const raw = this.raw();
    return raw !== null && Number.isNaN(parseDecimal(raw));
  });

  /** true si el número lleva más decimales de los permitidos */
  protected readonly tooManyDecimals = computed(() => {
    const raw = this.raw();
    const max = this.param().max_decimals;
    if (raw === null || max === null || max === undefined) return false;
    const value = parseDecimal(raw);
    return value !== null && !Number.isNaN(value) && decimalCount(value) > max;
  });

  /** Mostrar cómo se interpretó el número cuando lleva coma o punto */
  protected readonly showsReading = computed(() => {
    const raw = this.raw();
    return !!raw && /[.,]/.test(raw) && !this.isInvalid() && !this.tooManyDecimals() && typeof this.value() === 'number';
  });

  protected readonly decimalsText = computed(() => decimalsHint(this.param().max_decimals));

  protected onFocus(): void {
    this.focused.set(true);
    this.showHelp();
  }

  protected showHelp(): void {
    const p = this.param();
    const tips = [
      p.data_type === 'numero' && p.unit_of_measure ? `Escribe la cantidad en ${p.unit_of_measure}.` : null,
      p.data_type === 'numero' && p.max_decimals === 0 ? 'Solo números enteros, sin decimales.' : DATA_TYPE_TIP[p.data_type],
      p.data_type === 'numero' && p.max_decimals ? `Máximo ${decimalsHint(p.max_decimals)}.` : null,
      p.is_required ? 'Es obligatorio.' : 'Es opcional: puedes dejarlo vacío.',
      p.parameter_type === 'direccional' ? 'Se llena solo al elegir el punto en el mapa; puedes corregirlo.' : null,
    ].filter((t): t is string => !!t);

    this.store.pinHelp({
      icon: p.parameter_type === 'direccional' ? '📍' : '📏',
      eyebrow: p.parameter_type === 'direccional' ? 'Ubicación' : 'Parámetro',
      title: p.name,
      description: p.description || 'Este parámetro todavía no tiene una descripción cargada.',
      tips,
      isItem: true,
    });
  }

  protected onNumber(event: Event): void {
    const raw = (event.target as HTMLInputElement).value;
    this.raw.set(raw);
    const value = parseDecimal(raw);
    // Con más decimales de los permitidos no se guarda (queda como vacío hasta corregirlo)
    const ok = value !== null && !Number.isNaN(value) && !this.tooManyDecimals();
    this.store.setValue(this.param().id, ok ? value : null);
  }

  protected onText(event: Event): void {
    this.store.setValue(this.param().id, (event.target as HTMLInputElement).value);
  }

  protected onDate(event: Event): void {
    const value = (event.target as HTMLInputElement).value;
    this.store.setValue(this.param().id, value || null);
  }

  protected setBoolean(value: boolean): void {
    // Tocar otra vez la opción elegida la deja sin responder
    this.store.setValue(this.param().id, this.value() === value ? null : value);
    this.showHelp();
  }
}

/** Cuántos decimales tiene un número (120.5 → 1, 3 → 0) */
export function decimalCount(value: number): number {
  if (!Number.isFinite(value)) return 0;
  const text = String(value).includes('e') ? value.toFixed(20).replace(/0+$/, '') : String(value);
  const dot = text.indexOf('.');
  return dot < 0 ? 0 : text.length - dot - 1;
}

/** "1 decimal", "2 decimales"… (null/undefined = sin límite) */
function decimalsHint(max: number | null | undefined): string {
  if (max === null || max === undefined) return '';
  return max === 1 ? '1 decimal' : `${max} decimales`;
}

/**
 * Convierte lo escrito en número aceptando coma o punto como decimal.
 *  "120,5" / "120.5" → 120.5
 *  "1.234,5" / "1,234.5" → 1234.5 (el último separador es el decimal)
 *  "1.234.567" → 1234567 (separador repetido = miles)
 * Devuelve null si está vacío y NaN si no es un número válido (o es negativo).
 */
export function parseDecimal(raw: string): number | null {
  let text = raw.replace(/\s/g, '');
  if (text === '') return null;
  if (!/^[\d.,]+$/.test(text) || !/\d/.test(text)) return NaN;

  const lastDot = text.lastIndexOf('.');
  const lastComma = text.lastIndexOf(',');

  if (lastDot >= 0 && lastComma >= 0) {
    const decimal = lastDot > lastComma ? '.' : ',';
    const thousands = decimal === '.' ? ',' : '.';
    text = text.split(thousands).join('');
    if (text.split(decimal).length > 2) return NaN;
    text = text.replace(decimal, '.');
  } else {
    const sep = lastDot >= 0 ? '.' : lastComma >= 0 ? ',' : null;
    if (sep) {
      const parts = text.split(sep);
      // "1.234.567": varios separadores iguales, todos en grupos de 3 → miles
      if (parts.length > 2) {
        if (!parts.slice(1).every((p) => p.length === 3)) return NaN;
        text = parts.join('');
      } else {
        text = parts.join('.');
      }
    }
  }

  return Number(text);
}

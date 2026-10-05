import { Component, ElementRef, computed, inject, signal } from '@angular/core';

import { Standard } from '../../../../core/models/procedure.models';
import { WizardStore } from '../../wizard.store';

/** Buscar y agregar normas técnicas (el catálogo ya está cargado: se filtra en el navegador) */
@Component({
  selector: 'app-standard-picker',
  templateUrl: './standard-picker.html',
  host: { '(document:click)': 'onDocumentClick($event)' },
})
export class StandardPicker {
  protected readonly store = inject(WizardStore);
  private readonly host = inject(ElementRef<HTMLElement>);

  protected readonly query = signal('');
  protected readonly open = signal(false);

  /** Normas elegidas, en el orden en que se agregaron */
  protected readonly selected = computed(() => {
    const catalog = this.store.standardsCatalog();
    return this.store
      .standardIds()
      .map((id) => catalog.find((s) => s.id === id))
      .filter((s): s is Standard => !!s);
  });

  /** Normas que coinciden con lo escrito (sin las ya elegidas) */
  protected readonly available = computed(() => {
    const added = new Set(this.store.standardIds());
    const term = normalize(this.query().trim());
    return this.store
      .standardsCatalog()
      .filter((s) => !added.has(s.id))
      .filter((s) => !term || normalize(`${s.name} ${s.country ?? ''} ${s.description ?? ''}`).includes(term));
  });

  protected onInput(event: Event): void {
    this.query.set((event.target as HTMLInputElement).value);
    this.open.set(true);
  }

  protected onFocus(): void {
    this.open.set(true);
    this.store.pinHelp({
      icon: '📐',
      eyebrow: 'Norma técnica',
      title: '¿Qué norma elijo?',
      description:
        'Debes colocar la norma de diseño, verificación o consulta que corresponda al proyecto, informe o peritaje ' +
        'que estás realizando y presentando a la SIB La Paz. Es la referencia técnica que respalda los cálculos y ' +
        'criterios del trabajo, y aparecerá en la carátula como "Norma de diseño".',
      example: 'Proyecto eléctrico: NB 777\nEstructura de hormigón: ACI 318\nInforme o peritaje: la norma con la que se verificó o consultó',
      tips: [
        'Elige la norma que realmente usaste para diseñar, verificar o consultar el trabajo.',
        'Escribe parte del nombre o el país de la norma para buscarla.',
        'Haz clic en una norma para agregarla. Si usaste más de una, agrégalas todas.',
        'Si te equivocaste, quítala con ✕.',
      ],
      warning: 'Si no encuentras la norma en la lista, consulta en las oficinas de la SIB La Paz antes de enviar el trámite.',
      isItem: true,
    });
  }

  protected pickFirst(): void {
    const first = this.available()[0];
    if (first) this.pick(first);
  }

  protected pick(standard: Standard): void {
    this.store.toggleStandard(standard);
    this.query.set('');
    this.open.set(false);
  }

  protected onDocumentClick(event: MouseEvent): void {
    if (this.open() && !this.host.nativeElement.contains(event.target as Node)) {
      this.open.set(false);
    }
  }
}

/** Minúsculas y sin tildes, para que "calculo" encuentre "Cálculo" */
function normalize(text: string): string {
  return text.normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase();
}

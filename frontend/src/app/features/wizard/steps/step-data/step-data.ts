import { Component, computed, inject } from '@angular/core';

import { StepId } from '../../wizard.models';
import { WizardStore } from '../../wizard.store';
import { AddressMap } from './address-map';
import { EngineerPicker } from './engineer-picker';
import { OwnerPicker } from './owner-picker';
import { ParamInput } from './param-input';
import { StandardPicker } from './standard-picker';

@Component({
  selector: 'app-step-data',
  imports: [OwnerPicker, EngineerPicker, StandardPicker, ParamInput, AddressMap],
  templateUrl: './step-data.html',
})
export class StepData {
  protected readonly store = inject(WizardStore);

  /** La sección de ubicación (mapa) solo aparece si la terciaria tiene parámetros direccionales */
  protected readonly hasMap = computed(() => this.store.addressParameters().length > 0);
  /** Número de la sección siguiente a la ubicación */
  protected readonly nextNum = computed(() => (this.hasMap() ? 4 : 3));

  /** Errores 422 del backend al guardar, con el paso donde se corrigen */
  protected readonly issues = computed<{ message: string; step: StepId }[]>(() => {
    const error = this.store.saveError();
    if (!error) return [];
    return Object.entries(error.errors).map(([field, messages]) => ({
      message: messages[0],
      step: this.store.stepForField(field),
    }));
  });

  /** Guarda el borrador (o lo actualiza si hubo cambios) y pasa a subir los documentos */
  protected async saveAndContinue(): Promise<void> {
    if (!this.store.procedure() || this.store.dirty()) {
      if (!(await this.store.save())) return;
    }
    this.store.goTo(3);
  }

  protected titleFieldHelp(): void {
    this.store.pinHelp({
      icon: '🏷️',
      eyebrow: 'Título del proyecto',
      title: '¿Qué título escribo?',
      description: 'Es el nombre con el que se identificará el proyecto en la carátula.',
      example: 'VIVIENDA UNIFAMILIAR – FAMILIA MAMANI\nEDIFICIO MULTIFAMILIAR "LOS PINOS"',
      tips: ['Escribe qué es el proyecto y, si ayuda, de quién es.', 'Se guardará en MAYÚSCULAS.'],
      isItem: true,
    });
  }

  protected value(event: Event): string {
    return (event.target as HTMLInputElement).value;
  }

  protected onLocationToggle(event: Event): void {
    if (!(event.target as HTMLDetailsElement).open) return;
    this.store.pinHelp({
      icon: '📍',
      eyebrow: 'Opcional',
      title: 'Ubicación del proyecto',
      description: 'Ayuda a identificar el proyecto en la revisión. No aparece en la carátula.',
      example: 'Dirección: Av. 6 de Agosto N° 2170\nZona: Sopocachi\nMunicipio: La Paz',
      isItem: true,
    });
  }
}

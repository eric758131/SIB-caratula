import { Component, effect, inject, signal } from '@angular/core';

import { Topic } from '../../../../core/models/procedure.models';
import { CatalogService } from '../../../../core/services/catalog.service';
import { OptionList } from '../../../../shared/option-list/option-list';
import { WizardStore } from '../../wizard.store';

@Component({
  selector: 'app-step-category',
  imports: [OptionList],
  templateUrl: './step-category.html',
})
export class StepCategory {
  protected readonly store = inject(WizardStore);
  private readonly catalog = inject(CatalogService);

  /** Sugerencias para el tema de INFORMES (temas usados antes) */
  protected readonly topics = signal<Topic[]>([]);
  private topicsLoaded = false;

  constructor() {
    effect(() => {
      if (this.store.isInformes() && !this.topicsLoaded) {
        this.topicsLoaded = true;
        this.catalog.topics().subscribe({ next: (t) => this.topics.set(t), error: () => this.topics.set([]) });
      }
    });
  }

  protected value(event: Event): string {
    return (event.target as HTMLInputElement).value;
  }

  protected topicHelp(): void {
    this.store.pinHelp({
      icon: '📑',
      eyebrow: 'Informes',
      title: 'Tema del informe',
      description: 'Los informes no tienen una lista de tipos de proyecto. Escribe con tus palabras de qué trata el informe.',
      example: 'ESTUDIO DE SUELOS\nINFORME TÉCNICO DE ESTRUCTURA EXISTENTE',
      tips: [
        'Mientras escribes verás temas usados antes; elige uno si coincide para mantener el orden.',
        'Usa al menos 3 letras.',
      ],
      isItem: true,
    });
  }
}

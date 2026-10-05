import { Component, ElementRef, effect, inject, untracked, viewChild } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';

import { Caratula } from '../../shared/caratula/caratula';
import { HelpPanel } from './help-panel/help-panel';
import { StepCategory } from './steps/step-category/step-category';
import { StepData } from './steps/step-data/step-data';
import { StepDocuments } from './steps/step-documents/step-documents';
import { StepDownload } from './steps/step-download/step-download';
import { StepPreview } from './steps/step-preview/step-preview';
import { STEPS, StepId } from './wizard.models';
import { WizardStore } from './wizard.store';

/**
 * Asistente de trámites. La URL guarda el trámite en curso (?t=hash)
 * para que recargar la página o compartir el enlace no pierda el progreso.
 */
@Component({
  selector: 'app-wizard-page',
  imports: [StepCategory, StepData, StepPreview, StepDocuments, StepDownload, HelpPanel, Caratula],
  providers: [WizardStore],
  templateUrl: './wizard-page.html',
  host: {
    '(window:beforeprint)': 'onBeforePrint()',
    '(window:afterprint)': 'onAfterPrint()',
  },
})
export class WizardPage {
  protected readonly store = inject(WizardStore);
  private readonly router = inject(Router);
  private readonly route = inject(ActivatedRoute);

  protected readonly steps = STEPS;
  private readonly content = viewChild<ElementRef<HTMLElement>>('content');

  constructor() {
    this.store.init(this.route.snapshot.queryParamMap.get('t'));

    // Al cambiar de paso, volver arriba
    effect(() => {
      this.store.step();
      untracked(() => this.content()?.nativeElement.scrollTo({ top: 0, behavior: 'smooth' }));
    });

    // Mantener ?t=hash sincronizado con el trámite actual
    effect(() => {
      const hash = this.store.procedure()?.hash_code ?? null;
      if (this.store.resuming()) return;
      untracked(() => {
        if (this.route.snapshot.queryParamMap.get('t') !== hash) {
          void this.router.navigate([], { queryParams: { t: hash }, replaceUrl: true });
        }
      });
    });
  }

  protected isDone(id: StepId): boolean {
    return id < this.store.step() || (this.store.isLocked() && id < 5);
  }

  /**
   * Si el navegador imprime encabezados, que no salga el nombre de la página:
   * se deja el título en blanco mientras se imprime y luego se restaura.
   */
  private titleBeforePrint: string | null = null;

  protected onBeforePrint(): void {
    this.titleBeforePrint = document.title;
    document.title = ' ';
  }

  protected onAfterPrint(): void {
    if (this.titleBeforePrint !== null) document.title = this.titleBeforePrint;
    this.titleBeforePrint = null;
  }

  protected retry(): void {
    this.store.catalogError.set(null);
    void this.store.loadBase();
  }
}

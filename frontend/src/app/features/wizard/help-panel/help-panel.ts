import { Component, computed, inject, signal } from '@angular/core';

import { Caratula } from '../../../shared/caratula/caratula';
import { WizardStore } from '../wizard.store';

const NUMBERS = ['1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣'];

/** Columna derecha: "Asistente S.I.B." con ayudas visuales y carátula en vivo */
@Component({
  selector: 'app-help-panel',
  imports: [Caratula],
  templateUrl: './help-panel.html',
  host: { '(document:keydown)': 'onKey($event)' },
})
export class HelpPanel {
  protected readonly store = inject(WizardStore);
  protected readonly numbers = NUMBERS;

  /** La pestaña "Carátula" solo tiene sentido mientras se completan los datos */
  protected readonly showTabs = computed(() => this.store.step() <= 2);
  protected readonly showCaratula = computed(() => this.showTabs() && this.store.helpTab() === 'caratula');

  /** Visor de imágenes ampliado */
  protected readonly zoomIndex = signal<number | null>(null);
  protected readonly images = computed(() => {
    const h = this.store.activeHelp();
    return [h.image1, h.image2, h.image3].filter((src): src is string => !!src);
  });

  protected openZoom(src: string): void {
    this.zoomIndex.set(Math.max(0, this.images().indexOf(src)));
  }

  protected move(delta: number): void {
    const i = this.zoomIndex();
    const total = this.images().length;
    if (i === null || !total) return;
    this.zoomIndex.set((i + delta + total) % total);
  }

  protected onKey(event: KeyboardEvent): void {
    if (this.zoomIndex() === null) {
      if (event.key === 'Escape') this.store.helpOpen.set(false);
      return;
    }
    if (event.key === 'Escape') this.zoomIndex.set(null);
    if (event.key === 'ArrowRight') this.move(1);
    if (event.key === 'ArrowLeft') this.move(-1);
  }
}

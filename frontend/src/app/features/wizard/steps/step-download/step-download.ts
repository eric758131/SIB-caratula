import { Component, computed, inject } from '@angular/core';

import { Caratula } from '../../../../shared/caratula/caratula';
import { WizardStore } from '../../wizard.store';

@Component({
  selector: 'app-step-download',
  imports: [Caratula],
  templateUrl: './step-download.html',
})
export class StepDownload {
  protected readonly store = inject(WizardStore);

  protected readonly sentFiles = computed(() => {
    const p = this.store.procedure();
    if (!p) return [];
    return [
      ...p.required_documents.flatMap((d) => d.files.map((f) => ({ ...f, docName: d.name }))),
      ...p.extra_documents.map((f) => ({ ...f, docName: 'Documento adicional' })),
    ];
  });

  protected print(): void {
    window.print();
  }
}

import { Component, computed, inject, signal } from '@angular/core';

import { ApiError } from '../../../../core/utils/api-error';
import { Caratula } from '../../../../shared/caratula/caratula';
import { WizardStore, isEmptyValue } from '../../wizard.store';

@Component({
  selector: 'app-step-preview',
  imports: [Caratula],
  templateUrl: './step-preview.html',
})
export class StepPreview {
  protected readonly store = inject(WizardStore);
  protected readonly confirming = signal(false);

  /** Lista de verificación para que el usuario revise antes de enviar */
  protected readonly checks = computed(() => {
    const d = this.store.caratula();
    const filled = d.parameters.filter((p) => p.value !== null).length;
    // Solo los obligatorios deben estar llenos; los opcionales pueden quedar en blanco
    const requiredOk = !this.store.parameters().some((p) => p.is_required && isEmptyValue(this.store.values()[p.id]));
    const p = this.store.procedure();
    const docs = p?.required_documents ?? [];
    const files = docs.reduce((n, doc) => n + doc.files.length, 0) + (p?.extra_documents.length ?? 0);
    return [
      { ok: !!d.title, text: `Título: ${d.title || '—'}` },
      { ok: !!d.typeName, text: `Tipo: ${d.typeName || '—'}` },
      { ok: requiredOk, text: `${filled} de ${d.parameters.length} parámetro(s) llenos` },
      { ok: d.standards.length > 0, text: d.standards.length ? `Norma: ${d.standards.join(', ')}` : 'Sin norma (opcional)' },
      { ok: d.owners.length > 0, text: `${d.owners.length} interesado(s)` },
      { ok: d.engineers.length > 0, text: `${d.engineers.length} ingeniero(s) con espacio de firma` },
      { ok: this.store.missingDocuments().length === 0, text: `${files} archivo(s) subido(s)` },
    ];
  });

  protected async submit(): Promise<void> {
    this.confirming.set(false);
    await this.store.submit();
  }

  protected submitMessage(error: ApiError): string {
    return error.errors['documents']?.[0] ?? error.message;
  }

  protected print(): void {
    window.print();
  }
}

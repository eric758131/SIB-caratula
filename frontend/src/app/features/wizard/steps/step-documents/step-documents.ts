import { Component, DestroyRef, computed, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { EMPTY, catchError, concatMap, from, tap } from 'rxjs';

import { UploadedFileInfo } from '../../../../core/models/procedure.models';
import { ProcedureService } from '../../../../core/services/procedure.service';
import { toApiError } from '../../../../core/utils/api-error';
import { UPLOAD_EXTENSIONS, UPLOAD_MAX_MB } from '../../wizard.models';
import { WizardStore } from '../../wizard.store';

/** Zona de subida: un documento requerido (su id) o "otros documentos" del usuario */
export type DocKey = number | 'extra';

interface UploadItem {
  key: number;
  docId: DocKey;
  name: string;
  size: number;
  percent: number;
  status: 'queued' | 'uploading' | 'error';
  error: string | null;
}

@Component({
  selector: 'app-step-documents',
  templateUrl: './step-documents.html',
})
export class StepDocuments {
  protected readonly store = inject(WizardStore);
  private readonly procedures = inject(ProcedureService);
  private readonly destroyRef = inject(DestroyRef);

  protected readonly extensions = UPLOAD_EXTENSIONS;
  protected readonly maxMb = UPLOAD_MAX_MB;
  protected readonly accept = UPLOAD_EXTENSIONS.map((e) => '.' + e).join(',');

  protected readonly uploads = signal<UploadItem[]>([]);
  protected readonly overDoc = signal<DocKey | null>(null);
  protected readonly deleting = signal<number | null>(null);
  protected readonly deleteError = signal<string | null>(null);

  protected readonly docs = computed(() => this.store.procedure()?.required_documents ?? []);
  /** Documentos que el usuario agrega por su cuenta (no cuentan para poder enviar) */
  protected readonly extras = computed(() => this.store.procedure()?.extra_documents ?? []);
  protected readonly completeCount = computed(() => this.docs().filter((d) => d.files.length > 0).length);
  protected readonly missing = this.store.missingDocuments;
  protected readonly busy = computed(() => this.uploads().some((u) => u.status !== 'error'));
  protected readonly percent = computed(() =>
    this.docs().length ? Math.round((100 * this.completeCount()) / this.docs().length) : 100,
  );

  private nextKey = 1;

  protected uploadsFor(docId: DocKey): UploadItem[] {
    return this.uploads().filter((u) => u.docId === docId);
  }

  /* ---------- Selección y arrastre ---------- */
  protected onDragOver(docId: DocKey, event: DragEvent): void {
    event.preventDefault();
    this.overDoc.set(docId);
  }

  protected onDrop(docId: DocKey, event: DragEvent): void {
    event.preventDefault();
    this.overDoc.set(null);
    if (event.dataTransfer?.files.length) this.enqueue(docId, event.dataTransfer.files);
  }

  protected onPick(docId: DocKey, event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files?.length) this.enqueue(docId, input.files);
    input.value = '';
  }

  /** Valida en el navegador (formato y tamaño) y sube los válidos uno por uno */
  private enqueue(docId: DocKey, fileList: FileList): void {
    const hash = this.store.procedure()?.hash_code;
    if (!hash) return;

    const valid: { key: number; file: File }[] = [];
    const items: UploadItem[] = [];

    for (const file of Array.from(fileList)) {
      const key = this.nextKey++;
      const error = this.validate(file);
      items.push({ key, docId, name: file.name, size: file.size, percent: 0, status: error ? 'error' : 'queued', error });
      if (!error) valid.push({ key, file });
    }
    this.uploads.update((list) => [...list, ...items]);

    from(valid)
      .pipe(
        concatMap(({ key, file }) => {
          this.patch(key, { status: 'uploading' });
          return this.procedures.uploadDocument(hash, docId === 'extra' ? null : docId, file).pipe(
            tap((ev) => {
              if (ev.type === 'progress') {
                this.patch(key, { percent: ev.percent });
              } else {
                this.store.addUploadedFile(ev.file);
                this.uploads.update((list) => list.filter((u) => u.key !== key));
              }
            }),
            catchError((err) => {
              const e = toApiError(err);
              this.patch(key, { status: 'error', error: e.errors['file']?.[0] ?? e.errors['required_document_id']?.[0] ?? e.message });
              return EMPTY;
            }),
          );
        }),
        takeUntilDestroyed(this.destroyRef),
      )
      .subscribe();
  }

  private validate(file: File): string | null {
    const ext = file.name.split('.').pop()?.toLowerCase() ?? '';
    if (!UPLOAD_EXTENSIONS.includes(ext)) {
      return `Formato .${ext || '?'} no permitido. Usa: ${UPLOAD_EXTENSIONS.join(', ').toUpperCase()}.`;
    }
    if (file.size > UPLOAD_MAX_MB * 1024 * 1024) {
      return `Pesa ${this.size(file.size)}; el máximo es ${UPLOAD_MAX_MB} MB. Comprime el archivo o divídelo en partes.`;
    }
    if (file.size === 0) {
      return 'El archivo está vacío.';
    }
    return null;
  }

  private patch(key: number, changes: Partial<UploadItem>): void {
    this.uploads.update((list) => list.map((u) => (u.key === key ? { ...u, ...changes } : u)));
  }

  protected dismiss(key: number): void {
    this.uploads.update((list) => list.filter((u) => u.key !== key));
  }

  /* ---------- Archivos ya subidos ---------- */
  protected remove(file: UploadedFileInfo): void {
    const hash = this.store.procedure()?.hash_code;
    if (!hash) return;
    this.deleting.set(file.id);
    this.deleteError.set(null);
    this.procedures.deleteDocument(hash, file.id).subscribe({
      next: () => {
        this.store.removeUploadedFile(file.id);
        this.deleting.set(null);
      },
      error: (err) => {
        this.deleteError.set(toApiError(err).message);
        this.deleting.set(null);
      },
    });
  }

  protected icon(name: string): string {
    const ext = name.split('.').pop()?.toLowerCase() ?? '';
    if (ext === 'pdf') return '📕';
    if (['dwg', 'dxf'].includes(ext)) return '📐';
    if (['doc', 'docx'].includes(ext)) return '📝';
    if (['xls', 'xlsx'].includes(ext)) return '📊';
    if (['jpg', 'jpeg', 'png'].includes(ext)) return '🖼️';
    if (['zip', 'rar'].includes(ext)) return '🗜️';
    return '📄';
  }

  protected size(bytes: number): string {
    if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
  }
}

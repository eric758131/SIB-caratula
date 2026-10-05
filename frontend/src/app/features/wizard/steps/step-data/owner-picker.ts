import { Component, inject, signal } from '@angular/core';

import { CatalogService } from '../../../../core/services/catalog.service';
import { ApiError, fieldError, toApiError } from '../../../../core/utils/api-error';
import { WizardStore } from '../../wizard.store';

/** Registrar interesados (propietarios) escribiendo sus datos: no hay buscador */
@Component({
  selector: 'app-owner-picker',
  templateUrl: './owner-picker.html',
})
export class OwnerPicker {
  protected readonly store = inject(WizardStore);
  private readonly catalog = inject(CatalogService);

  protected readonly newName = signal('');
  protected readonly newPhone = signal('');
  protected readonly saving = signal(false);
  protected readonly error = signal<ApiError | null>(null);
  /** Aviso después de agregar (ya estaba registrado o ya estaba en la lista) */
  protected readonly notice = signal<string | null>(null);
  protected readonly fieldError = fieldError;

  protected onFocus(): void {
    this.store.pinHelp({
      icon: '👥',
      eyebrow: 'Interesados',
      title: '¿Quiénes son los interesados?',
      description:
        'Son los propietarios o solicitantes del proyecto: pueden ser personas o empresas. ' +
        'El nombre que registres aquí es exactamente el que aparecerá en la carátula, así que revísalo bien.',
      example: 'Persona: JUAN PÉREZ MAMANI\nEmpresa: CONSTRUCTORA LOS ANDES S.R.L.',
      tips: [
        'Escribe el nombre completo como figura en su carnet de identidad o, si es empresa, su razón social.',
        'Presiona "Agregar interesado". Si hay varios propietarios, agrégalos uno por uno.',
        'Marca con ★ al interesado principal: aparecerá primero en la carátula.',
        'El teléfono es opcional y no aparece en la carátula.',
      ],
      isItem: true,
    });
  }

  protected save(): void {
    if (this.saving() || this.newName().trim().length < 3) return;
    this.saving.set(true);
    this.error.set(null);
    this.notice.set(null);
    this.catalog.createOwner({ name: this.newName().trim(), phone: this.newPhone().trim() || null }).subscribe({
      next: (owner) => {
        // Si ya existía en el sistema, el backend devuelve ese mismo registro
        if (this.store.owners().some((o) => o.id === owner.id)) {
          this.notice.set(`${owner.name} ya está en la lista de interesados.`);
        } else {
          this.store.addOwner({ id: owner.id, name: owner.name, phone: owner.phone });
          if (owner.existing) this.notice.set(`${owner.name} ya estaba registrado: lo agregamos con sus datos.`);
        }
        this.newName.set('');
        this.newPhone.set('');
        this.saving.set(false);
      },
      error: (err) => {
        this.error.set(toApiError(err));
        this.saving.set(false);
      },
    });
  }

  protected value(event: Event): string {
    return (event.target as HTMLInputElement).value;
  }

  protected initials(name: string): string {
    return name
      .split(' ')
      .filter(Boolean)
      .slice(0, 2)
      .map((w) => w[0])
      .join('')
      .toUpperCase();
  }
}

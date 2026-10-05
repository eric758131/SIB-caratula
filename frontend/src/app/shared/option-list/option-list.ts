import { Component, input, output } from '@angular/core';

import { HelpInfo } from '../../core/models/procedure.models';

export type OptionItem = HelpInfo & { code?: string | null };

/**
 * Lista vertical de opciones seleccionables (categorías).
 * Emite `hovered` para que el panel de ayuda muestre la explicación al pasar el mouse.
 */
@Component({
  selector: 'app-option-list',
  templateUrl: './option-list.html',
})
export class OptionList<T extends OptionItem> {
  readonly items = input.required<T[]>();
  readonly selectedId = input<number | null>(null);
  readonly loading = input(false);
  readonly disabled = input(false);
  /** Mensaje cuando la lista está bloqueada (falta elegir la columna anterior) */
  readonly disabledText = input('');
  readonly emptyText = input('No hay opciones disponibles.');
  readonly label = input.required<string>();

  readonly picked = output<T>();
  readonly hovered = output<T | null>();

  protected hasHelp(item: T): boolean {
    return !!(item.description || item.example || item.important_notes || item.image_1 || item.image_2 || item.image_3);
  }
}

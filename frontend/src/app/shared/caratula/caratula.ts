import { Component, computed, input, signal } from '@angular/core';

import { environment } from '../../../environments/environment';

export interface CaratulaData {
  primaryName: string | null;
  primaryLogo: string | null;
  secondaryName: string | null;
  /** Código de la terciaria (arriba a la derecha) */
  tertiaryCode: string | null;
  /** Terciaria o, en INFORMES, el tema escrito por el usuario */
  typeName: string | null;
  title: string;
  parameters: { name: string; value: string | null; unit: string | null }[];
  standards: string[];
  owners: string[];
  /** El principal primero: es el que firma la carátula */
  engineers: { name: string; rni: string }[];
}

/**
 * Carátula tamaño carta. Lo que aún no se completó se muestra en gris
 * para que el usuario vea dónde irá cada dato.
 *
 *  [logo cat. 1]        [logo S.I.B.]          [COD. terciaria]
 *  VISADO / cat. 1
 *                     CATEGORÍA SECUNDARIA
 *                       Tipo de proyecto
 *               ┌ Título del proyecto y parámetros ┐
 *  ┌ Interesados ┐ ┌ Sello de conformidad (físico)  ┐
 *  ├ Sello y firma ┤ │                                │
 *  └ RNI / nombre ┘ └                                ┘
 */
@Component({
  selector: 'app-caratula',
  templateUrl: './caratula.html',
})
export class Caratula {
  readonly data = input.required<CaratulaData>();

  /** Solo firma el ingeniero principal (viene primero) */
  protected readonly engineer = computed<CaratulaData['engineers'][number] | null>(() => this.data().engineers[0] ?? null);

  protected readonly companyLogo = environment.companyLogo;
  protected readonly companyLogoFailed = signal(false);
  protected readonly primaryLogoFailed = signal<string | null>(null);
}

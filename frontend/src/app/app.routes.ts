import { Routes } from '@angular/router';

export const routes: Routes = [
  {
    path: '',
    title: 'Asistente de Carátulas · SIB La Paz',
    loadComponent: () => import('./features/wizard/wizard-page').then((m) => m.WizardPage),
  },
  { path: '**', redirectTo: '' },
];

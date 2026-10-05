import { Component, ElementRef, inject, signal } from '@angular/core';

import { environment } from '../../../environments/environment';
import { THEMES, ThemeService } from '../../core/services/theme.service';

@Component({
  selector: 'app-header',
  templateUrl: './header.html',
  host: {
    '(document:click)': 'onDocumentClick($event)',
    '(document:keydown.escape)': 'open.set(false)',
  },
})
export class Header {
  protected readonly themeService = inject(ThemeService);
  private readonly host = inject(ElementRef<HTMLElement>);

  protected readonly logo = environment.companyLogo;
  protected readonly logoFailed = signal(false);
  protected readonly themes = THEMES;
  protected readonly open = signal(false);

  protected onDocumentClick(event: MouseEvent): void {
    if (this.open() && !this.host.nativeElement.contains(event.target as Node)) {
      this.open.set(false);
    }
  }
}

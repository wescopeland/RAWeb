import type { Alpine } from 'alpinejs';

import type { attachTooltipToElement as AttachTooltipToElement } from '@/tooltip';
import type { handleLeaderboardTabClick as HandleLeaderboardTabClick } from '@/utils/handleLeaderboardTabClick';
import type { injectShortcode as InjectShortcode } from '@/utils/injectShortcode';
import type { toggleUserCompletedSetsVisibility as ToggleUserCompletedSetsVisibility } from '@/utils/toggleUserCompletedSetsVisibility';

declare global {
  var Alpine: Alpine;
  var attachTooltipToElement: typeof AttachTooltipToElement;
  var cfg: Record<string, unknown> | undefined;
  var copyToClipboard: (text: string) => void;
  var handleLeaderboardTabClick: typeof HandleLeaderboardTabClick;
  var clipboard: (text: string) => void;
  var injectShortcode: typeof InjectShortcode;
  var showStatusSuccess: (message: string) => void;
  var toggleUserCompletedSetsVisibility: typeof ToggleUserCompletedSetsVisibility;
}

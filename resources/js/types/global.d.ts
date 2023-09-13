import type { Alpine } from 'alpinejs';

import { autoExpandTextInput as AutoExpandTextInput } from '@/utils/autoExpandTextInput';
import type {
  modalComponent as ModalComponent,
  newsCarouselComponent as NewsCarouselComponent,
  hideEarnedCheckboxComponent as HideEarnedCheckboxComponent,
  tooltipComponent as TooltipComponent,
} from '@/alpine';
import type { fetcher as Fetcher } from '@/utils/fetcher';
import type { getStringByteCount as GetStringByteCount } from '@/utils/getStringByteCount';
import type { handleLeaderboardTabClick as HandleLeaderboardTabClick } from '@/utils/handleLeaderboardTabClick';
import type { initializeTextareaCounter as InitializeTextareaCounter } from '@/utils/initializeTextareaCounter';
import type { injectShortcode as InjectShortcode } from '@/utils/injectShortcode';
import type { loadPostPreview as LoadPostPreview } from '@/utils/loadPostPreview';
import type { setCookie as SetCookie } from '@/utils/cookie';
import type { toggleUserCompletedSetsVisibility as ToggleUserCompletedSetsVisibility } from '@/utils/toggleUserCompletedSetsVisibility';
import type { updateUrlParameter as UpdateUrlParameter } from '@/utils/updateUrlParameter';

declare global {
  var Alpine: Alpine;
  var assetUrl: string;
  var autoExpandTextInput: typeof AutoExpandTextInput;
  var cachedDialogHtmlContent: string | undefined;
  var cfg: Record<string, unknown> | undefined;
  var copyToClipboard: (text: string) => void;
  var fetcher: typeof Fetcher;
  var getStringByteCount: typeof GetStringByteCount;
  var handleLeaderboardTabClick: typeof HandleLeaderboardTabClick;
  var hideEarnedCheckboxComponent: typeof HideEarnedCheckboxComponent;
  var initializeTextareaCounter: typeof InitializeTextareaCounter;
  var injectShortcode: typeof InjectShortcode;
  var loadPostPreview: typeof LoadPostPreview;
  var modalComponent: typeof ModalComponent;
  var newsCarouselComponent: typeof NewsCarouselComponent;
  var setCookie: typeof SetCookie;
  var showStatusFailure: (message: string) => void;
  var showStatusSuccess: (message: string) => void;
  var toggleUserCompletedSetsVisibility: typeof ToggleUserCompletedSetsVisibility;
  var tooltipComponent: typeof TooltipComponent;
  var updateUrlParameter: typeof UpdateUrlParameter;
}

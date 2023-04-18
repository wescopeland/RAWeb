import { asset, fetcher } from '../../utils';

import { tooltipStore as store } from '../state/tooltipStore';
import { renderTooltip } from './renderTooltip';
import { pinTooltipToCursorPosition } from './pinTooltipToCursorPosition';

export async function loadDynamicTooltip(
  anchorEl: HTMLElement,
  type: string,
  id: string,
  context?: unknown,
  givenX?: number,
  givenY?: number
): Promise<void> {
  const cacheKey = `${type}_${id}`;

  // Store the current anchorEl. This helps us avoid potential race
  // conditions if the user quickly moves over multiple dynamic tooltips.
  store.activeAnchorEl = anchorEl;

  if (store.dynamicContentCache[cacheKey]) {
    displayDynamicTooltip(anchorEl, store.dynamicContentCache[cacheKey], givenY);
    return;
  }

  // Temporarily show a loading spinner.
  const genericLoadingTemplate = /** @html */ `
    <div>
      <div class="flex justify-center items-center w-8 h-8 p-5">
        <img src="${asset('/assets/images/icon/loading.gif')}" alt="Loading">
      </div>
    </div>
  `;
  renderTooltip(anchorEl, genericLoadingTemplate, (givenX ?? 0) + 12, (givenY ?? 0) + 12, {
    isBorderless: true,
  });

  store.dynamicTimeoutId = setTimeout(async () => {
    const fetchedDynamicContent = await fetchDynamicTooltipContent(type, id, context);

    if (fetchedDynamicContent) {
      store.dynamicContentCache[cacheKey] = fetchedDynamicContent;

      // We don't want to continue on with displaying this dynamic tooltip
      // if a static tooltip is opened while we're fetching data.
      const wasTimeoutCleared = !store.dynamicTimeoutId;
      if (anchorEl === store.activeAnchorEl && !wasTimeoutCleared) {
        renderTooltip(anchorEl, fetchedDynamicContent, givenX, givenY);
        pinTooltipToCursorPosition(
          anchorEl,
          store.tooltipEl,
          store.trackedMouseX,
          (store.trackedMouseY ?? 0) - 12 // The tooltip appears to jump if we don't do this subtraction.
        );
      }
    }
  }, 200);
}

async function fetchDynamicTooltipContent(type: string, id: string, context?: unknown) {
  const contentResponse = await fetcher<{ html: string }>('/request/card.php', {
    method: 'POST',
    body: `type=${type}&id=${id}&context=${context}`,
  });

  return contentResponse.html;
}

function displayDynamicTooltip(anchorEl: HTMLElement, htmlContent: string, givenY?: number) {
  const setX = store.trackedMouseX;
  let setY = store.trackedMouseY;

  if (givenY) {
    setY = givenY - 10;
  }

  renderTooltip(anchorEl, htmlContent);
  pinTooltipToCursorPosition(anchorEl, store.tooltipEl, setX, setY);
}

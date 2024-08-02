import { usePage } from '@inertiajs/react';
import { useMutation } from '@tanstack/react-query';
import type { AxiosResponse } from 'axios';
import axios from 'axios';
import { type FC, useState } from 'react';
import { LuAlertCircle, LuCopy } from 'react-icons/lu';
import { useCopyToClipboard } from 'react-use';

import { BaseButton } from '@/common/components/+vendor/BaseButton';
import { toast } from '@/common/components/+vendor/BaseToaster';
import {
  BaseTooltip,
  BaseTooltipContent,
  BaseTooltipTrigger,
} from '@/common/components/+vendor/BaseTooltip';

import type { SettingsPageProps } from '../../models';

export const ManageWebApiKey: FC = () => {
  const {
    props: { user },
  } = usePage<SettingsPageProps>();

  const [, copyToClipboard] = useCopyToClipboard();

  const [currentWebApiKey, setCurrentWebApiKey] = useState(user.apiKey);

  const mutation = useMutation({
    mutationFn: () => {
      return axios.delete<any, AxiosResponse<{ newKey: string }>>('/settings/keys/web');
    },
    onSuccess: ({ data }) => {
      setCurrentWebApiKey(data.newKey);
    },
  });

  const handleCopyApiKeyClick = () => {
    copyToClipboard(currentWebApiKey);
    toast.success('Copied your web API key!');
  };

  const handleResetApiKeyClick = () => {
    if (!confirm('Are you sure you want to reset your web API key? This cannot be reversed.')) {
      return;
    }

    toast.promise(mutation.mutateAsync(), {
      loading: 'Resetting...',
      success: 'Your web API key has been reset.',
      error: 'Something went wrong.',
    });
  };

  return (
    <div className="grid grid-cols-4 w-full">
      <p className="w-48 text-menu-link">Web API Key</p>

      <div className="flex flex-col gap-2 w-full col-span-3">
        <BaseTooltip>
          <BaseTooltipTrigger asChild>
            <BaseButton className="flex gap-2" onClick={handleCopyApiKeyClick}>
              <LuCopy />
              <span className="font-mono">{safeFormatApiKey(currentWebApiKey)}</span>
            </BaseButton>
          </BaseTooltipTrigger>

          <BaseTooltipContent>Copy to clipboard</BaseTooltipContent>
        </BaseTooltip>

        <div>
          <p>
            This is your <span className="italic">personal</span> web API key. Handle it with care.
          </p>
          <p>
            API documentation can be found{' '}
            <a href="https://api-docs.retroachievements.org" target="_blank" rel="noreferrer">
              here
            </a>
            .
          </p>
        </div>

        <BaseButton
          className="flex gap-2 max-w-fit"
          size="sm"
          variant="destructive"
          onClick={handleResetApiKeyClick}
        >
          <LuAlertCircle className="text-lg" />
          Reset Web API Key
        </BaseButton>
      </div>
    </div>
  );
};

/**
 * If someone is sharing their screen, we don't want them to accidentally
 * leak their entire web API key.
 */
function safeFormatApiKey(apiKey: string): string {
  // For safety, but this should never happen.
  if (apiKey.length <= 12) {
    return apiKey;
  }

  // "AAAAAA...123456"
  return `${apiKey.slice(0, 6)}...${apiKey.slice(-6)}`;
}
import { useMutation } from '@tanstack/react-query';
import axios from 'axios';
import { type FC } from 'react';
import { LuAlertCircle } from 'react-icons/lu';

import { BaseButton } from '@/common/components/+vendor/BaseButton';
import { toast } from '@/common/components/+vendor/BaseToaster';

export const ManageConnectApiKey: FC = () => {
  const mutation = useMutation({
    mutationFn: () => {
      return axios.delete('/settings/keys/connect');
    },
  });

  const handleResetApiKeyClick = () => {
    if (
      !confirm(
        'Are you sure you want to reset your Connect API key? This will log you out of all emulators.',
      )
    ) {
      return;
    }

    toast.promise(mutation.mutateAsync(), {
      loading: 'Resetting...',
      success: 'Your Connect API key has been reset.',
      error: 'Something went wrong.',
    });
  };

  return (
    <div className="grid grid-cols-4">
      <p className="w-48 text-menu-link">Connect API Key</p>

      <div className="col-span-3 flex flex-col gap-2">
        <p>
          Your Connect API key is used by emulators to keep you logged in. Resetting the key will
          log you out of all emulators.
        </p>

        <BaseButton
          className="flex gap-2 max-w-fit"
          size="sm"
          variant="destructive"
          onClick={handleResetApiKeyClick}
        >
          <LuAlertCircle className="text-lg" />
          Reset Connect API Key
        </BaseButton>
      </div>
    </div>
  );
};

import { zodResolver } from '@hookform/resolvers/zod';
import { useMutation } from '@tanstack/react-query';
import axios from 'axios';
import { useForm } from 'react-hook-form';
import { z } from 'zod';

import { toast } from '@/common/components/+vendor/BaseToaster';

import { convertObjectToWebsitePrefs } from '../../utils/convertObjectToWebsitePrefs';
import { convertWebsitePrefsToObject } from '../../utils/convertWebsitePrefsToObject';

// TODO use a UserPreference const written by laravel-typescript-transformer
// These are hardcoded right now - we need to write a new transformer to properly
// convert these values to TypeScript constants so they can be used correctly.

/** @see UserPreference.php */

const notificationsFormSchema = z.object({
  '0': z.boolean(), // EmailOn_ActivityComment
  '1': z.boolean(), // EmailOn_AchievementComment
  '2': z.boolean(), // EmailOn_UserWallComment
  '3': z.boolean(), // EmailOn_ForumReply
  '4': z.boolean(), // EmailOn_Followed
  '5': z.boolean(), // EmailOn_PrivateMessage
  '6': z.boolean(), // EmailOn_Newsletter
  '7': z.boolean(), // -- Site_SuppressMatureContentWarning
  '8': z.boolean(), // SiteMsgOn_ActivityComment
  '9': z.boolean(), // SiteMsgOn_AchievementComment
  '10': z.boolean(), // SiteMsgOn_UserWallComment
  '11': z.boolean(), // SiteMsgOn_ForumReply
  '12': z.boolean(), // SiteMsgOn_Followed
  '13': z.boolean(), // -- SiteMsgOn_PrivateMessage
  '14': z.boolean(), // -- SiteMsgOn_Newsletter
  '15': z.boolean(), // -- Forum_ShowAbsoluteDates
  '16': z.boolean(), // -- Game_HideMissableIndicators
  '17': z.boolean(), // -- User_OnlyContactFromFollowing
});

export type FormValues = z.infer<typeof notificationsFormSchema>;

export function useNotificationsSectionForm(websitePrefs: number) {
  const form = useForm<FormValues>({
    resolver: zodResolver(notificationsFormSchema),
    defaultValues: convertWebsitePrefsToObject(websitePrefs),
  });

  const mutation = useMutation({
    mutationFn: (websitePrefs: number) => {
      return axios.put('/settings/preferences', { websitePrefs });
    },
  });

  const onSubmit = (formValues: FormValues) => {
    toast.promise(mutation.mutateAsync(convertObjectToWebsitePrefs(formValues)), {
      loading: 'Updating...',
      success: 'Updated.',
      error: 'Something went wrong.',
    });
  };

  return { form, mutation, onSubmit };
}

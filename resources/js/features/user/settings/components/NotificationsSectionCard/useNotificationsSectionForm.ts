import { zodResolver } from '@hookform/resolvers/zod';
import { useMutation } from '@tanstack/react-query';
import axios from 'axios';
import { useForm } from 'react-hook-form';
import { route } from 'ziggy-js';
import { z } from 'zod';

import { toastMessage } from '@/common/components/+vendor/BaseToaster';
import { StringifiedUserPreference } from '@/common/utils/generatedAppConstants';

import { convertObjectToWebsitePrefs } from '../../utils/convertObjectToWebsitePrefs';
import { convertWebsitePrefsToObject } from '../../utils/convertWebsitePrefsToObject';

const notificationsFormSchema = z.object({
  [StringifiedUserPreference.EmailOn_ActivityComment]: z.boolean(),
  [StringifiedUserPreference.EmailOn_AchievementComment]: z.boolean(),
  [StringifiedUserPreference.EmailOn_UserWallComment]: z.boolean(),
  [StringifiedUserPreference.EmailOn_ForumReply]: z.boolean(),
  [StringifiedUserPreference.EmailOn_Followed]: z.boolean(),
  [StringifiedUserPreference.EmailOn_PrivateMessage]: z.boolean(),
  [StringifiedUserPreference.EmailOn_Newsletter]: z.boolean(),
  [StringifiedUserPreference.Site_SuppressMatureContentWarning]: z.boolean(),
  [StringifiedUserPreference.SiteMsgOn_ActivityComment]: z.boolean(),
  [StringifiedUserPreference.SiteMsgOn_AchievementComment]: z.boolean(),
  [StringifiedUserPreference.SiteMsgOn_UserWallComment]: z.boolean(),
  [StringifiedUserPreference.SiteMsgOn_ForumReply]: z.boolean(),
  [StringifiedUserPreference.SiteMsgOn_Followed]: z.boolean(),
  [StringifiedUserPreference.SiteMsgOn_PrivateMessage]: z.boolean(),
  [StringifiedUserPreference.SiteMsgOn_Newsletter]: z.boolean(),
  [StringifiedUserPreference.Forum_ShowAbsoluteDates]: z.boolean(),
  [StringifiedUserPreference.Game_HideMissableIndicators]: z.boolean(),
  [StringifiedUserPreference.User_OnlyContactFromFollowing]: z.boolean(),
});

export type FormValues = z.infer<typeof notificationsFormSchema>;

export function useNotificationsSectionForm(websitePrefs: number) {
  const form = useForm<FormValues>({
    resolver: zodResolver(notificationsFormSchema),
    defaultValues: convertWebsitePrefsToObject(websitePrefs),
  });

  const mutation = useMutation({
    mutationFn: (websitePrefs: number) => {
      return axios.put(route('settings.preferences.update'), { websitePrefs });
    },
  });

  const onSubmit = (formValues: FormValues) => {
    toastMessage.promise(mutation.mutateAsync(convertObjectToWebsitePrefs(formValues)), {
      loading: 'Updating...',
      success: 'Updated.',
      error: 'Something went wrong.',
    });
  };

  return { form, mutation, onSubmit };
}

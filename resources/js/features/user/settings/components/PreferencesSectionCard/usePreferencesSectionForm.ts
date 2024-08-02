import { zodResolver } from '@hookform/resolvers/zod';
import { useMutation } from '@tanstack/react-query';
import axios from 'axios';
import { useForm } from 'react-hook-form';
import { z } from 'zod';

import { toast } from '@/common/components/+vendor/BaseToaster';
import { StringifiedUserPreference } from '@/common/utils/generatedAppConstants';

import { convertObjectToWebsitePrefs } from '../../utils/convertObjectToWebsitePrefs';
import { convertWebsitePrefsToObject } from '../../utils/convertWebsitePrefsToObject';

const settingsFormSchema = z.object({
  [StringifiedUserPreference.Site_SuppressMatureContentWarning]: z.boolean(),
  [StringifiedUserPreference.Forum_ShowAbsoluteDates]: z.boolean(),
  [StringifiedUserPreference.Game_HideMissableIndicators]: z.boolean(),
  [StringifiedUserPreference.User_OnlyContactFromFollowing]: z.boolean(),
});

export type FormValues = z.infer<typeof settingsFormSchema>;

export function usePreferencesSectionForm(websitePrefs: number) {
  const form = useForm<FormValues>({
    resolver: zodResolver(settingsFormSchema),
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

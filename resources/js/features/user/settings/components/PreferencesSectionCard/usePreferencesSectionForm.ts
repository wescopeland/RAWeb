import { zodResolver } from '@hookform/resolvers/zod';
import { useMutation } from '@tanstack/react-query';
import axios from 'axios';
import { useForm } from 'react-hook-form';
import { route } from 'ziggy-js';
import type { z } from 'zod';

import { toastMessage } from '@/common/components/+vendor/BaseToaster';

import { convertObjectToWebsitePrefs } from '../../utils/convertObjectToWebsitePrefs';
import { convertWebsitePrefsToObject } from '../../utils/convertWebsitePrefsToObject';
import { websitePrefsFormSchema } from '../../utils/websitePrefsFormSchema';

export type FormValues = z.infer<typeof websitePrefsFormSchema>;

export function usePreferencesSectionForm(websitePrefs: number) {
  const form = useForm<FormValues>({
    resolver: zodResolver(websitePrefsFormSchema),
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

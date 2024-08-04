import { zodResolver } from '@hookform/resolvers/zod';
import { useMutation } from '@tanstack/react-query';
import axios from 'axios';
import { useForm } from 'react-hook-form';
import { route } from 'ziggy-js';
import { z } from 'zod';

import { toast } from '@/common/components/+vendor/BaseToaster';

const changeEmailAddressFormSchema = z
  .object({
    newEmail: z.string().email(),
    confirmEmail: z.string().email(),
  })
  .refine((data) => data.newEmail === data.confirmEmail, {
    message: 'Email addresses must match.',
    path: ['confirmEmail'],
  });

type FormValues = z.infer<typeof changeEmailAddressFormSchema>;

export function useChangeEmailAddressForm(props: {
  setCurrentEmailAddress: React.Dispatch<React.SetStateAction<string>>;
}) {
  const form = useForm<FormValues>({
    resolver: zodResolver(changeEmailAddressFormSchema),
    defaultValues: {
      newEmail: '',
      confirmEmail: '',
    },
  });

  const mutation = useMutation({
    mutationFn: (formValues: FormValues) => {
      return axios.put(route('settings.email.update'), formValues);
    },
    onSuccess: () => {
      props.setCurrentEmailAddress(form.getValues().newEmail);
    },
  });

  const onSubmit = (formValues: FormValues) => {
    toast.promise(mutation.mutateAsync(formValues), {
      loading: 'Changing email address...',
      success: 'Changed email address!',
      error: 'Something went wrong.',
    });
  };

  return { form, mutation, onSubmit };
}

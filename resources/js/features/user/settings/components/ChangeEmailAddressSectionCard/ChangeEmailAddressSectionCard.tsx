import { usePage } from '@inertiajs/react';
import { type FC, useState } from 'react';

import {
  BaseFormControl,
  BaseFormField,
  BaseFormItem,
  BaseFormLabel,
  BaseFormMessage,
} from '@/common/components/+vendor/BaseForm';
import { BaseInput } from '@/common/components/+vendor/BaseInput';

import type { SettingsPageProps } from '../../models';
import { SectionFormCard } from '../SectionFormCard';
import { useChangeEmailAddressForm } from './useChangeEmailAddressForm';

export const ChangeEmailAddressSectionCard: FC = () => {
  const {
    props: { user },
  } = usePage<SettingsPageProps>();

  const [currentEmailAddress, setCurrentEmailAddress] = useState(user.emailAddress);

  const { form, mutation, onSubmit } = useChangeEmailAddressForm({ setCurrentEmailAddress });

  return (
    <SectionFormCard
      title="Change Email"
      formMethods={form}
      onSubmit={onSubmit}
      isSubmitting={mutation.isPending}
    >
      <div className="flex flex-col gap-5">
        <div className="flex w-full items-center">
          <p className="w-2/5 text-menu-link">Current Email Address</p>
          <p>{currentEmailAddress}</p>
        </div>

        <BaseFormField
          control={form.control}
          name="newEmail"
          render={({ field }) => (
            <BaseFormItem className="flex w-full items-center">
              <BaseFormLabel className="w-2/5 text-menu-link">New Email Address</BaseFormLabel>

              <div className="flex flex-grow flex-col gap-1">
                <BaseFormControl>
                  <BaseInput
                    type="email"
                    placeholder="enter your new email address here..."
                    required
                    {...field}
                  />
                </BaseFormControl>

                <BaseFormMessage />
              </div>
            </BaseFormItem>
          )}
        />

        <BaseFormField
          control={form.control}
          name="confirmEmail"
          render={({ field }) => (
            <BaseFormItem className="flex w-full items-center">
              <BaseFormLabel className="w-2/5 text-menu-link">
                Confirm New Email Address
              </BaseFormLabel>

              <div className="flex flex-grow flex-col gap-1">
                <BaseFormControl>
                  <BaseInput
                    type="email"
                    placeholder="confirm your new email address here..."
                    required
                    {...field}
                  />
                </BaseFormControl>

                <BaseFormMessage />
              </div>
            </BaseFormItem>
          )}
        />
      </div>
    </SectionFormCard>
  );
};

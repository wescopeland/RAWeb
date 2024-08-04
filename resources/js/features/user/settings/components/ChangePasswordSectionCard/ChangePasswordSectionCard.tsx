import type { FC } from 'react';

import {
  BaseFormControl,
  BaseFormDescription,
  BaseFormField,
  BaseFormItem,
  BaseFormLabel,
  BaseFormMessage,
} from '@/common/components/+vendor/BaseForm';
import { BaseInput } from '@/common/components/+vendor/BaseInput';

import { SectionFormCard } from '../SectionFormCard';
import { useChangePasswordForm } from './useChangePasswordForm';

export const ChangePasswordSectionCard: FC = () => {
  const { form, mutation, onSubmit } = useChangePasswordForm();

  return (
    <SectionFormCard
      headingLabel="Change Password"
      formMethods={form}
      onSubmit={onSubmit}
      isSubmitting={mutation.isPending}
    >
      <div className="flex flex-col gap-5">
        <BaseFormField
          control={form.control}
          name="currentPassword"
          render={({ field }) => (
            <BaseFormItem className="flex w-full items-center">
              <BaseFormLabel className="w-2/5 text-menu-link">Current Password</BaseFormLabel>

              <div className="flex flex-grow flex-col gap-1">
                <BaseFormControl>
                  <BaseInput
                    type="password"
                    placeholder="enter your current password here..."
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
          name="newPassword"
          render={({ field }) => (
            <BaseFormItem className="flex w-full items-center">
              <BaseFormLabel className="w-2/5 text-menu-link">New Password</BaseFormLabel>

              <div className="flex flex-grow flex-col gap-1">
                <BaseFormControl>
                  <BaseInput
                    type="password"
                    placeholder="enter a new password here..."
                    required
                    minLength={8}
                    {...field}
                  />
                </BaseFormControl>

                <BaseFormDescription>Must be at least 8 characters.</BaseFormDescription>

                <BaseFormMessage />
              </div>
            </BaseFormItem>
          )}
        />

        <BaseFormField
          control={form.control}
          name="confirmPassword"
          render={({ field }) => (
            <BaseFormItem className="flex w-full items-center">
              <BaseFormLabel className="w-2/5 text-menu-link">Confirm Password</BaseFormLabel>

              <div className="flex flex-grow flex-col gap-1">
                <BaseFormControl>
                  <BaseInput
                    type="password"
                    placeholder="confirm your new password here..."
                    required
                    minLength={8}
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

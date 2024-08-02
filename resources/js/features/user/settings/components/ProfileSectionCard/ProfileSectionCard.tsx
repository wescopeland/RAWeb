import { usePage } from '@inertiajs/react';
import type { FC } from 'react';

import {
  BaseFormControl,
  BaseFormDescription,
  BaseFormField,
  BaseFormItem,
  BaseFormLabel,
} from '@/common/components/+vendor/BaseForm';
import { BaseInput } from '@/common/components/+vendor/BaseInput';
import { BaseSwitch } from '@/common/components/+vendor/BaseSwitch';

import type { SettingsPageProps } from '../../models';
import { SectionFormCard } from '../SectionFormCard';
import { useProfileSectionForm } from './useProfileSectionForm';

export const ProfileSectionCard: FC = () => {
  const {
    props: { user },
  } = usePage<SettingsPageProps>();

  const { form, mutation, onSubmit } = useProfileSectionForm({
    motto: user.motto,
    userWallActive: user.userWallActive,
  });

  return (
    <SectionFormCard
      title="Profile"
      formMethods={form}
      onSubmit={onSubmit}
      isSubmitting={mutation.isPending}
    >
      <div className="flex flex-col gap-5">
        <div className="flex w-full items-center">
          <p className="w-2/5 text-menu-link">Roles</p>
          <p>Moderator</p>
        </div>

        <BaseFormField
          control={form.control}
          name="motto"
          render={({ field }) => (
            <BaseFormItem className="flex w-full items-center">
              <BaseFormLabel className="w-2/5 text-menu-link">User Motto</BaseFormLabel>

              <div className="flex flex-grow flex-col gap-1">
                <BaseFormControl>
                  <BaseInput maxLength={50} {...field} />
                </BaseFormControl>
                <BaseFormDescription className="w-full flex justify-between">
                  <span>No profanity.</span>
                  <span>{field.value.length}/50</span>
                </BaseFormDescription>
              </div>
            </BaseFormItem>
          )}
        />

        <BaseFormField
          control={form.control}
          name="userWallActive"
          render={({ field }) => (
            <BaseFormItem className="flex items-center w-full">
              <BaseFormLabel className="w-2/5 text-menu-link">
                Allow Comments on my User Wall
              </BaseFormLabel>

              <BaseFormControl>
                <BaseSwitch checked={field.value} onCheckedChange={field.onChange} />
              </BaseFormControl>
            </BaseFormItem>
          )}
        />
      </div>
    </SectionFormCard>
  );
};

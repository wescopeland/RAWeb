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
      headingLabel="Profile"
      formMethods={form}
      onSubmit={onSubmit}
      isSubmitting={mutation.isPending}
    >
      <div className="@container @xl:gap-5 flex flex-col gap-7">
        <div className="@xl:flex-row @xl:items-center flex w-full flex-col">
          <p className="@xl:w-2/5 text-menu-link">Visible Role</p>
          <p>Moderator</p>
        </div>

        <BaseFormField
          control={form.control}
          name="motto"
          render={({ field }) => (
            <BaseFormItem className="@xl:flex-row @xl:items-center flex w-full flex-col gap-1">
              <BaseFormLabel className="@xl:w-2/5 text-menu-link">User Motto</BaseFormLabel>

              <div className="flex flex-grow flex-col gap-1">
                <BaseFormControl>
                  <BaseInput maxLength={50} placeholder="enter a motto here..." {...field} />
                </BaseFormControl>

                <BaseFormDescription className="flex w-full justify-between">
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
            <BaseFormItem className="@xl:flex-row @xl:items-center flex w-full flex-col gap-1">
              <BaseFormLabel className="@xl:w-2/5 text-menu-link">
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

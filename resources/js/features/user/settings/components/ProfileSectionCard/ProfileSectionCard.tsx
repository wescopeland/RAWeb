import { usePage } from '@inertiajs/react';
import type { FC } from 'react';

import {
  BaseFormControl,
  BaseFormDescription,
  BaseFormField,
  BaseFormLabel,
} from '@/common/components/+vendor/BaseForm';
import { BaseInput } from '@/common/components/+vendor/BaseInput';
import { BaseSwitch } from '@/common/components/+vendor/BaseSwitch';

import type { SettingsPageProps } from '../../models';
import { SettingsFormCard } from '../SettingsFormCard/SettingsFormCard';
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
    <SettingsFormCard
      title="Profile"
      formMethods={form}
      onSubmit={onSubmit}
      isSubmitting={mutation.isPending}
    >
      <table>
        <tbody className="[&>tr>td]:py-2 [&>tr]:!bg-embed [&>tr>th]:!px-0 [&>tr>td]:!px-0">
          <tr>
            <th className="w-[40%]">Roles</th>
            <td>Moderator</td>
          </tr>

          <BaseFormField
            control={form.control}
            name="motto"
            render={({ field }) => (
              <tr>
                <th>
                  <BaseFormLabel>User Motto</BaseFormLabel>
                </th>
                <td>
                  <BaseFormControl className="mb-1">
                    <BaseInput maxLength={50} {...field} />
                  </BaseFormControl>
                  <BaseFormDescription className="w-full flex justify-between">
                    <span>No profanity.</span>
                    <span>{field.value.length}/50</span>
                  </BaseFormDescription>
                </td>
              </tr>
            )}
          />

          <BaseFormField
            control={form.control}
            name="userWallActive"
            render={({ field }) => (
              <tr>
                <th>
                  <BaseFormLabel>Allow Comments on my User Wall</BaseFormLabel>
                </th>
                <td>
                  <BaseFormControl>
                    <BaseSwitch checked={field.value} onCheckedChange={field.onChange} />
                  </BaseFormControl>
                </td>
              </tr>
            )}
          />
        </tbody>
      </table>
    </SettingsFormCard>
  );
};

import { type FC, useId } from 'react';
import { useFormContext } from 'react-hook-form';

import { BaseCheckbox } from '@/common/components/+vendor/BaseCheckbox';
import { BaseFormControl, BaseFormField } from '@/common/components/+vendor/BaseForm';
import { BaseLabel } from '@/common/components/+vendor/BaseLabel';

import type { FormValues as NotificationsSectionFormValues } from './useNotificationsSectionForm';

interface NotificationsTableRowProps {
  label: string;

  emailFieldName?: keyof NotificationsSectionFormValues;
  siteFieldName?: keyof NotificationsSectionFormValues;
}

export const NotificationsTableRow: FC<NotificationsTableRowProps> = ({
  label,
  emailFieldName,
  siteFieldName,
}) => {
  const { control } = useFormContext<NotificationsSectionFormValues>();

  const emailId = useId();
  const siteId = useId();

  return (
    <tr>
      <th className="w-[40%]">{label}</th>

      <td>
        <div className="flex items-center gap-2">
          {emailFieldName ? (
            <BaseFormField
              control={control}
              name={emailFieldName}
              render={({ field }) => (
                <>
                  <BaseFormControl>
                    <BaseCheckbox
                      id={emailId}
                      checked={field.value}
                      onCheckedChange={field.onChange}
                    />
                  </BaseFormControl>

                  <BaseLabel htmlFor={emailId}>Email me</BaseLabel>
                </>
              )}
            />
          ) : null}
        </div>
      </td>

      <td>
        <div className="flex items-center gap-2">
          {siteFieldName ? (
            <BaseFormField
              control={control}
              name={siteFieldName}
              render={({ field }) => (
                <>
                  <BaseFormControl>
                    <BaseCheckbox
                      id={siteId}
                      checked={field.value}
                      onCheckedChange={field.onChange}
                    />
                  </BaseFormControl>

                  <BaseLabel htmlFor={siteId}>Notify me on RetroAchievements</BaseLabel>
                </>
              )}
            />
          ) : null}
        </div>
      </td>
    </tr>
  );
};

import { usePage } from '@inertiajs/react';
import type { FC } from 'react';

import type { SettingsPageProps } from '../../models';
import { SettingsFormCard } from '../SettingsFormCard/SettingsFormCard';
import { NotificationsTableRow } from './NotificationsTableRow';
import { useNotificationsSectionForm } from './useNotificationsSectionForm';

export const NotificationsSectionCard: FC = () => {
  const {
    props: { user },
  } = usePage<SettingsPageProps>();

  const { form, mutation, onSubmit } = useNotificationsSectionForm(user.websitePrefs);

  return (
    <SettingsFormCard
      title="Notifications"
      formMethods={form}
      onSubmit={onSubmit}
      isSubmitting={mutation.isPending}
    >
      <table>
        <tbody className="[&>tr>td]:py-2 [&>tr]:!bg-embed [&>tr>th]:!px-0 [&>tr>td]:!px-0">
          <NotificationsTableRow
            label="Comments on my activity"
            emailFieldName="0"
            siteFieldName="8"
          />

          <NotificationsTableRow
            label="Comments on an achievement I created"
            emailFieldName="1"
            siteFieldName="9"
          />

          <NotificationsTableRow
            label="Comments on my user wall"
            emailFieldName="2"
            siteFieldName="10"
          />

          <NotificationsTableRow
            label="Comments on a forum topic I'm involved in"
            emailFieldName="3"
            siteFieldName="11"
          />

          <NotificationsTableRow label="Someone follows me" emailFieldName="4" siteFieldName="12" />

          <NotificationsTableRow label="I receive a private message" emailFieldName="5" />
        </tbody>
      </table>
    </SettingsFormCard>
  );
};

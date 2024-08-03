import { usePage } from '@inertiajs/react';
import type { FC } from 'react';

import { StringifiedUserPreference } from '@/common/utils/generatedAppConstants';

import type { SettingsPageProps } from '../../models';
import { SectionFormCard } from '../SectionFormCard';
import { NotificationsTableRow } from './NotificationsTableRow';
import { useNotificationsSectionForm } from './useNotificationsSectionForm';

export const NotificationsSectionCard: FC = () => {
  const {
    props: { user },
  } = usePage<SettingsPageProps>();

  const { form, mutation, onSubmit } = useNotificationsSectionForm(user.websitePrefs);

  return (
    <SectionFormCard
      title="Notifications"
      formMethods={form}
      onSubmit={onSubmit}
      isSubmitting={mutation.isPending}
    >
      <table>
        <tbody className="[&>tr>td]:!px-0 [&>tr>td]:py-2 [&>tr>th]:!px-0 [&>tr]:!bg-embed">
          <NotificationsTableRow
            label="Comments on my activity"
            emailFieldName={StringifiedUserPreference.EmailOn_ActivityComment}
            siteFieldName={StringifiedUserPreference.SiteMsgOn_ActivityComment}
          />

          <NotificationsTableRow
            label="Comments on an achievement I created"
            emailFieldName={StringifiedUserPreference.EmailOn_AchievementComment}
            siteFieldName={StringifiedUserPreference.SiteMsgOn_AchievementComment}
          />

          <NotificationsTableRow
            label="Comments on my user wall"
            emailFieldName={StringifiedUserPreference.EmailOn_UserWallComment}
            siteFieldName={StringifiedUserPreference.SiteMsgOn_UserWallComment}
          />

          <NotificationsTableRow
            label="Comments on a forum topic I'm involved in"
            emailFieldName={StringifiedUserPreference.EmailOn_ForumReply}
            siteFieldName={StringifiedUserPreference.SiteMsgOn_ForumReply}
          />

          <NotificationsTableRow
            label="Someone follows me"
            emailFieldName={StringifiedUserPreference.EmailOn_Followed}
            siteFieldName={StringifiedUserPreference.SiteMsgOn_Followed}
          />

          <NotificationsTableRow
            label="I receive a private message"
            emailFieldName={StringifiedUserPreference.EmailOn_PrivateMessage}
          />
        </tbody>
      </table>
    </SectionFormCard>
  );
};

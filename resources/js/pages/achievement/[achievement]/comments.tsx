import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { AppLayout } from '@/common/layouts/AppLayout';
import type { AppPage } from '@/common/models';
import { AchievementCommentsMainRoot } from '@/features/comments/AchievementCommentsMainRoot';

const AchievementComments: AppPage<App.Community.Data.AchievementCommentsPageProps> = ({
  achievement,
}) => {
  const { t } = useTranslation();

  const metaDescription = `General discussion about the achievement ${achievement.title}`;

  return (
    <>
      <Head title={t('Comments - {{achievementTitle}}', { achievementTitle: achievement.title })}>
        <meta name="description" content={metaDescription} />
        <meta name="og:description" content={metaDescription} />

        <meta property="og:image" content={achievement.badgeUnlockedUrl} />
        <meta property="og:type" content="retroachievements:comment-list" />
      </Head>

      <AppLayout.Main>
        <AchievementCommentsMainRoot />
      </AppLayout.Main>
    </>
  );
};

AchievementComments.layout = (page) => <AppLayout withSidebar={false}>{page}</AppLayout>;

export default AchievementComments;

import { AppLayout } from '@/common/layouts/AppLayout';
import { AppPage } from '@/common/models';
import { RecentPostsMainRoot } from '@/forums/components/RecentPostsMainRoot';

const RecentPosts: AppPage = () => {
  return (
    <AppLayout.Main>
      <RecentPostsMainRoot />
    </AppLayout.Main>
  );
};

RecentPosts.layout = (page) => <AppLayout withSidebar={false}>{page}</AppLayout>;

export default RecentPosts;

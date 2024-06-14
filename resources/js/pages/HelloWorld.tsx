import { AppLayout } from '@/common/layouts/AppLayout';
import type { AppPage } from '@/common/models';

const HelloWorld: AppPage = (props) => {
  return (
    <>
      <AppLayout.Main>
        <p>hi</p>
      </AppLayout.Main>

      <AppLayout.Sidebar>stuff</AppLayout.Sidebar>
    </>
  );
};

HelloWorld.layout = (page) => <AppLayout withSidebar>{page}</AppLayout>;

export default HelloWorld;

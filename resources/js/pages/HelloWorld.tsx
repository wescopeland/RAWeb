import { AppLayout } from '@/common/layouts/AppLayout';
import type { AppPage } from '@/common/models';

const HelloWorld: AppPage = () => {
  return <p>{route().current()}</p>;
};

HelloWorld.layout = (page) => <AppLayout>{page}</AppLayout>;

export default HelloWorld;

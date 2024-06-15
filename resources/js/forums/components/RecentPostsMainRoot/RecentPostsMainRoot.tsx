import { usePage } from '@inertiajs/react';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import utc from 'dayjs/plugin/utc';
import type { FC } from 'react';

import { UserAvatar } from '@/common/components/UserAvatar';
import type { RecentPostsPageProps } from '@/forums/models';

import { RecentPostsBreadcrumbs } from './RecentPostsBreadcrumbs';

dayjs.extend(utc);
dayjs.extend(relativeTime);

export const RecentPostsMainRoot: FC = () => {
  const { recentForumPosts } = usePage<RecentPostsPageProps>().props;

  console.log(recentForumPosts);

  return (
    <div>
      <RecentPostsBreadcrumbs />

      <h1 className="w-full">Recent Posts</h1>

      <div className="hidden lg:block">
        <table className="table-highlight">
          <thead>
            <tr className="do-not-highlight">
              <th>Last Post By</th>
              <th>Message</th>
              <th className="whitespace-nowrap text-right">Additional Posts</th>
            </tr>
          </thead>

          <tbody>
            {recentForumPosts.map((recentForumPost) => (
              <tr key={recentForumPost.commentID}>
                <td className="py-3">
                  <UserAvatar displayName={recentForumPost.author} />
                </td>

                <td>
                  <p className="flex items-center gap-x-2">
                    <a
                      href={`/viewtopic.php?t=${recentForumPost.forumTopicID}&c=${recentForumPost.commentID}#${recentForumPost.commentID}`}
                    >
                      {recentForumPost.forumTopicTitle}
                    </a>
                    <span className="smalldate">
                      {dayjs.utc(recentForumPost.postedAt).fromNow()}
                    </span>
                  </p>

                  <div className="comment text-overflow-wrap">
                    <p className="lg:line-clamp-2 xl:line-clamp-1">{recentForumPost.shortMsg}</p>
                  </div>
                </td>

                <td></td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

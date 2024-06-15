import { PageProps } from '@inertiajs/core';

interface RecentForumPost {
  author: string;
  authorDisplayName: string | null;
  authorId: string | null;
  commentID: string | null;
  commentID1d: string | null;
  commentID7d: string | null;
  forumID: string;
  forumTitle: string;
  forumTopicID: string;
  forumTopicTitle: string;
  isTruncated: '0' | '1';
  postedAt: string;
  shortMsg: string;
}

export interface RecentPostsPageProps extends PageProps {
  maxPerPage: number;
  nextPageUrl: string;
  previousPageUrl: string | null;
  recentForumPosts: RecentForumPost[];
}

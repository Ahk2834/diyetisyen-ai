import {defineConfig} from 'vite';

const githubBase = `/${process.env.GITHUB_REPOSITORY?.split('/')[1] ?? 'diyetisyen-ai'}/`;
const hostingPath = process.env.HOSTING_BASE_PATH;
const normalizedHostingBase = hostingPath
  ? `/${hostingPath.replace(/^\/+|\/+$/g, '')}/`
  : '/';

export default defineConfig({
  base: process.env.GITHUB_PAGES === 'true' ? githubBase : normalizedHostingBase,
});

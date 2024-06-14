import { jsx, jsxs, Fragment } from "react/jsx-runtime";
const AppLayoutBase = ({ children, withSidebar = true }) => {
  return /* @__PURE__ */ jsx("div", { className: "container", children: /* @__PURE__ */ jsx("main", { className: withSidebar ? "with-sidebar" : void 0, "data-scroll-target": true, children }) });
};
const AppLayoutMain = ({ children }) => {
  return /* @__PURE__ */ jsx("article", { children });
};
const AppLayoutSidebar = ({ children }) => {
  return /* @__PURE__ */ jsx("aside", { children });
};
const AppLayout = Object.assign(AppLayoutBase, {
  Main: AppLayoutMain,
  Sidebar: AppLayoutSidebar
});
const HelloWorld = (props) => {
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(AppLayout.Main, { children: /* @__PURE__ */ jsx("p", { children: "hi" }) }),
    /* @__PURE__ */ jsx(AppLayout.Sidebar, { children: "stuff" })
  ] });
};
HelloWorld.layout = (page) => /* @__PURE__ */ jsx(AppLayout, { withSidebar: true, children: page });
export {
  HelloWorld as default
};

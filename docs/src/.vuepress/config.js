const { description } = require("../../package");
module.exports = {
    /**
     * Ref：https://v1.vuepress.vuejs.org/config/#title
     */
    title: "Muzzie",

    /**
     * Ref：https://v1.vuepress.vuejs.org/config/#description
     */
    description: "Welcome to muzzie documentation",

    /**
     * Extra tags to be injected to the page HTML `<head>`
     *
     * ref：https://v1.vuepress.vuejs.org/config/#head
     */
    head: [
        ["meta", { name: "theme-color", content: "#3eaf7c" }],
        [
            "link",
            { rel: "icon", type: "image/png", href: "/assets/img/logo.png" }
        ],
        ["meta", { name: "apple-mobile-web-app-capable", content: "yes" }],
        [
            "meta",
            { name: "apple-mobile-web-app-status-bar-style", content: "black" }
        ]
    ],

    /**
     * Theme configuration, here is the default theme configuration for VuePress.
     *
     * ref：https://v1.vuepress.vuejs.org/theme/default-theme-config.html
     */
    themeConfig: {
        logo: "/assets/img/logo.png",
        repo: "",
        editLinks: false,
        docsDir: "",
        editLinkText: "",
        sidebarDepth: 2,
        lastUpdated: false,
        search: false,
        sidebar: {
            "/": [
                {
                    title: "Requirements",
                    path: "requirements.md",
                    collapsable: false,
                    children: []
                },
                {
                    title: "Installation",
                    path: "/installation",
                    collapsable: false,
                    children: [
                        {
                            title: "VPS",
                            path: "/installation/vps"
                        },
                        {
                            title: "CPanel",
                            path: "/installation/cpanel"
                        },
                        {
                            title: "Local installation",
                            path: "/installation/local-machine"
                        }
                        // {
                        //   title: "I can/could not install",
                        //   path: '/installation/help'
                        // }
                    ]
                },
                {
                    title: "Guide",
                    path: "/guide/",
                    collapsable: false,
                    children: [
                        {
                            title: "Users & priviliges",
                            path: "/guide/users_and_privileges",
                            collapsable: false
                        },
                        {
                            title: "Pages & sections",
                            path: "/guide/pages-and-sections/",
                            collapsable: false,
                            children: [
                                {
                                    title: "How to create a page",
                                    path:
                                        "/guide/pages-and-sections/how-to-create-custom-page"
                                },
                                {
                                    title: "How to add a section on a page",
                                    path:
                                        "/guide/pages-and-sections/how-to-add-section"
                                }
                            ]
                        },
                        {
                            title: "Navigation",
                            path: "/guide/navigation/",
                            collapsable: false,
                            children: [
                                {
                                    title: "How to link a page to a nav item",
                                    path:
                                        "/guide/navigation/how-to-link-page-to-nav-item"
                                }
                            ]
                        },
                        {
                            title: "Content creation & management",
                            path: "/guide/content-creation-and-management/",
                            collapsable: false,
                            children: [
                                {
                                    title: "How to upload songs",
                                    path:
                                        "/guide/content-creation-and-management/how-to-upload-songs"
                                },
                                {
                                    title: "How to create albums & podcasts",
                                    path:
                                        "/guide/content-creation-and-management/how-to-create-albums"
                                },
                                {
                                    title: "How to request an artist account",
                                    path:
                                        "/guide/content-creation-and-management/how-to-request-artist-account"
                                }
                            ]
                        }
                    ]
                },
                {
                    title: "Changelog",
                    path: "/changelog/",
                    collapsable: false,
                    children: [
                        {
                            title: "V2",
                            path: "/changelog/2",
                            collapsable: false
                        },
                        {
                            title: "V3",
                            path: "/changelog/3",
                            collapsable: false
                        },
                        {
                            title: "V3.1",
                            path: "/changelog/3.1",
                            collapsable: false
                        },
                        {
                            title: "V3.2",
                            path: "/changelog/3.2",
                            collapsable: false
                        },
                        {
                            title: "V3.3",
                            path: "/changelog/3.3",
                            collapsable: false
                        },
                        {
                            title: "V3.5",
                            path: "/changelog/3.5",
                            collapsable: false
                        }
                    ]
                },
                {
                    title: "Upcoming",
                    path: "/upcoming/",
                    collapsable: false,
                    children: [
                        {
                            title: "V3.4",
                            path: "/upcoming/3.4",
                            collapsable: false
                        },
                        {
                            title: "V3.5",
                            path: "/upcoming/3.5",
                            collapsable: false
                        }
                    ]
                },
                {
                    title: "FAQ",
                    path: "/FAQ",
                    collapsable: false,
                    children: [
                        {
                            title: "My PWA is not working correcly",
                            path: "/FAQ/my-pwa-is-not-working"
                        },
                        {
                            title: "Can I disable podcasts?",
                            path: "/FAQ/can-i-disable-podcasts"
                        },
                        {
                            title: "Do I need Youtube api key?",
                            path: "/FAQ/do-i-need-youtube-api-key"
                        },
                        {
                            title: "Can I change Muzzie's default theme?",
                            path: "/FAQ/can-i-change-muzzie-default-theme"
                        }
                    ]
                },
                {
                    title: "Help",
                    path: "/help",
                    collapsable: false,
                    children: [
                        {
                            title: "Bugs & issues report",
                            path: "/help/bug-report"
                        },
                        {
                            title: "I want extra customization",
                            path: "/help/customization"
                        }
                    ]
                }
            ]
        }
    },
    /**
     * Apply plugins，ref：https://v1.vuepress.vuejs.org/zh/plugin/
     */
    plugins: ["@vuepress/plugin-back-to-top", "@vuepress/plugin-medium-zoom"]
};

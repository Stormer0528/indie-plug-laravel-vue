/**
 * Copyright 2018 Google Inc. All Rights Reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// If the loader is already loaded, just stop.
if (!self.define) {
  let registry = {};

  // Used for `eval` and `importScripts` where we can't get script URL by other means.
  // In both cases, it's safe to use a global var because those functions are synchronous.
  let nextDefineUri;

  const singleRequire = (uri, parentUri) => {
    uri = new URL(uri + ".js", parentUri).href;
    return registry[uri] || (
      
        new Promise(resolve => {
          if ("document" in self) {
            const script = document.createElement("script");
            script.src = uri;
            script.onload = resolve;
            document.head.appendChild(script);
          } else {
            nextDefineUri = uri;
            importScripts(uri);
            resolve();
          }
        })
      
      .then(() => {
        let promise = registry[uri];
        if (!promise) {
          throw new Error(`Module ${uri} didn’t register its module`);
        }
        return promise;
      })
    );
  };

  self.define = (depsNames, factory) => {
    const uri = nextDefineUri || ("document" in self ? document.currentScript.src : "") || location.href;
    if (registry[uri]) {
      // Module is already loading or loaded.
      return;
    }
    let exports = {};
    const require = depUri => singleRequire(depUri, uri);
    const specialDeps = {
      module: { uri },
      exports,
      require
    };
    registry[uri] = Promise.all(depsNames.map(
      depName => specialDeps[depName] || require(depName)
    )).then(deps => {
      factory(...deps);
      return exports;
    });
  };
}
define(['./workbox-270e9e46'], (function (workbox) { 'use strict';

  self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
      self.skipWaiting();
    }
  });

  /**
   * The precacheAndRoute() method efficiently caches and responds to
   * requests for URLs in the manifest.
   * See https://goo.gl/S9QRab
   */
  workbox.precacheAndRoute([{
    "url": "//installer/js/app.js",
    "revision": "3a5edcddb96a7e84be872e073c28bd1f"
  }, {
    "url": "//js/app.js",
    "revision": "1f79894af07c63f76eb16b572f3f0fda"
  }, {
    "url": "/css/app.css",
    "revision": "00bf567d350feb9f29aee65f647f8317"
  }, {
    "url": "/js/chunks/03dd95f3ea2582b5.js",
    "revision": null
  }, {
    "url": "/js/chunks/062f2c63d9238e3b.js",
    "revision": null
  }, {
    "url": "/js/chunks/0afa993c9985a16b.js",
    "revision": null
  }, {
    "url": "/js/chunks/10926dd507055001.js",
    "revision": null
  }, {
    "url": "/js/chunks/17ccbb1719c19efd.js",
    "revision": null
  }, {
    "url": "/js/chunks/1d5c207996cd92ec.js",
    "revision": null
  }, {
    "url": "/js/chunks/2013b19ecd1410c3.js",
    "revision": null
  }, {
    "url": "/js/chunks/25417898386e3afb.js",
    "revision": null
  }, {
    "url": "/js/chunks/2734b5f549631421.js",
    "revision": null
  }, {
    "url": "/js/chunks/319793ad30e5a8ba.js",
    "revision": null
  }, {
    "url": "/js/chunks/3667cc1551401de8.js",
    "revision": null
  }, {
    "url": "/js/chunks/37ed116585768cd0.js",
    "revision": null
  }, {
    "url": "/js/chunks/3b4fff0d12d17849.js",
    "revision": null
  }, {
    "url": "/js/chunks/3ef83ebfde5e4644.js",
    "revision": null
  }, {
    "url": "/js/chunks/44e4c72e25bec53b.js",
    "revision": null
  }, {
    "url": "/js/chunks/46b80dcda43267a3.js",
    "revision": null
  }, {
    "url": "/js/chunks/4ca0bb7223d2768f.js",
    "revision": null
  }, {
    "url": "/js/chunks/53b23a77a71d2698.js",
    "revision": null
  }, {
    "url": "/js/chunks/676a0a525dca01c7.js",
    "revision": null
  }, {
    "url": "/js/chunks/6c14dcaa0727a1e7.js",
    "revision": null
  }, {
    "url": "/js/chunks/73c0655ba6da902e.js",
    "revision": null
  }, {
    "url": "/js/chunks/7f4a78046c249362.js",
    "revision": null
  }, {
    "url": "/js/chunks/868a010129914e75.js",
    "revision": null
  }, {
    "url": "/js/chunks/87fd7a5ea3624687.js",
    "revision": null
  }, {
    "url": "/js/chunks/94edbc0c1ace0931.js",
    "revision": null
  }, {
    "url": "/js/chunks/9e46f9cf25d271f8.js",
    "revision": null
  }, {
    "url": "/js/chunks/a8035ce377070e11.js",
    "revision": null
  }, {
    "url": "/js/chunks/b747f5347c718e59.js",
    "revision": null
  }, {
    "url": "/js/chunks/b759b900a744883a.js",
    "revision": null
  }, {
    "url": "/js/chunks/c2f6229c666b5f14.js",
    "revision": null
  }, {
    "url": "/js/chunks/e2a6ae9aba4a0dfc.js",
    "revision": null
  }, {
    "url": "/js/chunks/ed3ad422949b54c2.js",
    "revision": null
  }, {
    "url": "/js/chunks/eda949c3e6cd073e.js",
    "revision": null
  }, {
    "url": "/js/chunks/fa901f10e6d0c357.js",
    "revision": null
  }, {
    "url": "/js/chunks/ff6c6fded462bfca.js",
    "revision": null
  }], {});

}));

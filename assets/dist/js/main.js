const C=()=>typeof window<"u"&&window.Pickprism||{},w=e=>String(e??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;");function M(e){const t=e.thumbnail?`<span class="search-result__thumb"><img src="${w(e.thumbnail)}" alt="" loading="lazy" decoding="async"></span>`:'<span class="search-result__thumb"></span>';return`
    <a href="${w(e.url)}" class="search-result" role="option">
      ${t}
      <span class="search-result__body">
        <span class="search-result__title">${w(e.title)}</span>
        <span class="search-result__excerpt">${w(e.excerpt||"")}</span>
      </span>
    </a>
  `}function P(e,t){const{i18n:n={}}=C();if(!t||!t.items||t.items.length===0){e.innerHTML=`<div class="search-empty">${w(n.noResults||"Ничего не найдено")}</div>`;return}const r=t.items.map(M).join(""),a=t.viewAll?`<a class="search-view-all" href="${w(t.viewAll)}">${w(n.showAll||"Показать все результаты")} →</a>`:"";e.innerHTML=r+a}function I(){const e=document.querySelector("[data-search]");if(!e)return;const t=document.querySelector(".pa-header"),n=document.querySelector("[data-search-toggle]"),r=e.querySelector("[data-search-close]"),a=e.querySelector("[data-search-input]"),c=e.querySelector("[data-search-dropdown]");if(!n||!a||!c)return;const{restUrl:o,nonce:u,searchMinLen:i=2,searchDebounce:s=300,i18n:l={}}=C();let d=null,p=0,f=!1;const $=()=>{c.hidden=!0},y=()=>{c.hidden=!1},g=()=>{f||(f=!0,e.classList.add("is-open"),e.setAttribute("aria-hidden","false"),a.setAttribute("tabindex","0"),t&&t.classList.add("pa-header--searching"),setTimeout(()=>a.focus(),260))},_=()=>{f&&(f=!1,e.classList.remove("is-open"),e.setAttribute("aria-hidden","true"),a.setAttribute("tabindex","-1"),t&&t.classList.remove("pa-header--searching"),$(),d&&d.abort())};if(n.addEventListener("click",m=>{m.preventDefault(),g()}),r&&r.addEventListener("click",m=>{m.preventDefault(),_()}),document.addEventListener("keydown",m=>{m.key==="Escape"&&f&&_()}),document.addEventListener("click",m=>{f&&(e.contains(m.target)||n.contains(m.target)||_())}),!o)return;const k=m=>{d&&d.abort(),d=new AbortController,c.innerHTML=`<div class="search-loading">${w(l.searching||"Ищем…")}</div>`,y();const T=`${o}search?q=${encodeURIComponent(m)}`,E={Accept:"application/json"};u&&(E["X-WP-Nonce"]=u),fetch(T,{headers:E,credentials:"same-origin",signal:d.signal}).then(b=>{if(!b.ok)throw new Error(`HTTP ${b.status}`);return b.json()}).then(b=>P(c,b)).catch(b=>{b.name!=="AbortError"&&(c.innerHTML=`<div class="search-empty">${w(l.errorGeneric||"Ошибка")}</div>`)})};a.addEventListener("input",()=>{const m=a.value.trim();if(clearTimeout(p),m.length<i){d&&d.abort(),$();return}p=setTimeout(()=>k(m),s)})}const B=()=>typeof window<"u"&&window.Pickprism||{},v=e=>String(e??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;");function N(e){const t=typeof e.hue=="number"?e.hue:24,n=e.primaryCategory?e.primaryCategory.name:"",r=n?n.charAt(0).toUpperCase():"P";let a="";return e.thumbnail&&e.thumbnail.url?a=`
      <div class="ha-cover">
        <img class="ha-cover__img"
          src="${v(e.thumbnail.url)}"
          ${e.thumbnail.srcset?`srcset="${v(e.thumbnail.srcset)}"`:""}
          ${e.thumbnail.sizes?`sizes="${v(e.thumbnail.sizes)}"`:""}
          width="${v(e.thumbnail.width||800)}"
          height="${v(e.thumbnail.height||500)}"
          loading="lazy"
          decoding="async"
          alt="${v(e.thumbnail.alt||e.title)}"
        />
        ${n?`<span class="ha-cover__cat">${v(n)}</span>`:""}
      </div>
    `:a=`
      <div class="ha-cover" style="--hue: ${t};">
        <div class="ha-cover__bg" aria-hidden="true"></div>
        <div class="ha-cover__letter" aria-hidden="true">${v(r)}</div>
        ${n?`<span class="ha-cover__cat">${v(n)}</span>`:""}
      </div>
    `,`
    <a class="ha-card reveal" href="${v(e.url)}" data-post-id="${v(e.id)}">
      ${a}
      <div class="ha-card__body">
        <div class="ha-card__meta">
          ${e.isNew?'<span class="ha-card__new">Новое</span>':""}
          <span class="ha-card__date"><time datetime="${v(e.dateIso)}">${v(e.date)}</time></span>
          <span class="ha-card__dot" aria-hidden="true">·</span>
          <span class="ha-card__read">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            ${v(e.readTime||1)} мин
          </span>
        </div>
        <h3 class="ha-card__title">${v(e.title)}</h3>
        ${e.excerpt?`<p class="ha-card__excerpt">${v(e.excerpt)}</p>`:""}
      </div>
    </a>
  `}function j(){const e=document.querySelector("[data-feed-container]");if(!e)return;const t=e.querySelector("[data-feed-list]"),n=e.querySelector("[data-feed-sentinel]"),r=e.querySelector("[data-feed-load-more]"),a=e.querySelector("[data-feed-status]"),c=e.querySelector(".pagination__links");if(!t)return;const{restUrl:o,feed:u,i18n:i={}}=B();if(!o||!u)return;let s=(u.paged||1)+1,l=!1,d=!1,p=null;c&&(c.hidden=!0),n&&(n.hidden=!1),r&&(r.hidden=!1);const f=y=>{a&&(a.textContent=y||"")},$=()=>{if(l||d)return;l=!0,f(i.loading||"Загружаем…"),r&&(r.disabled=!0);const y=new URLSearchParams({type:window.Pickprism&&window.Pickprism.feed&&window.Pickprism.feed.type||u.type||"home",value:window.Pickprism&&window.Pickprism.feed&&window.Pickprism.feed.value||u.value||"",paged:String(s),per_page:String(u.perPage||12)});fetch(`${o}feed?${y.toString()}`,{credentials:"same-origin",headers:{Accept:"application/json"}}).then(g=>{if(!g.ok)throw new Error(`HTTP ${g.status}`);return g.json()}).then(g=>{const _=g.items||[];if(_.length===0&&!g.hasMore){d=!0,f(i.endOfFeed||"Это все статьи"),r&&(r.hidden=!0),p&&n&&p.unobserve(n);return}const k=document.createDocumentFragment(),m=document.createElement("div");for(m.innerHTML=_.map(N).join("");m.firstChild;)k.appendChild(m.firstChild);t.appendChild(k),window.dispatchEvent(new CustomEvent("pickprism:reveal-refresh")),s+=1,g.hasMore?f(""):(d=!0,f(i.endOfFeed||"Это все статьи"),r&&(r.hidden=!0),p&&n&&p.unobserve(n))}).catch(()=>{f(i.errorGeneric||"Ошибка")}).finally(()=>{l=!1,r&&(r.disabled=!1)})};r&&r.addEventListener("click",$),n&&"IntersectionObserver"in window&&(p=new IntersectionObserver(y=>{for(const g of y)g.isIntersecting&&$()},{rootMargin:"400px 0px"}),p.observe(n)),window.addEventListener("pickprism:feed-reset",y=>{s=2,d=!(y.detail&&y.detail.hasMore),r&&(r.hidden=d),n&&p&&(d?p.unobserve(n):p.observe(n)),f(d?i.endOfFeed||"Это все статьи":"")})}const D=()=>typeof window<"u"&&window.matchMedia&&window.matchMedia("(prefers-reduced-motion: reduce)").matches;let q=null;function L(){q&&document.querySelectorAll(".reveal:not([data-reveal])").forEach(e=>{q.observe(e)})}function H(){if(D()||!("IntersectionObserver"in window)){document.querySelectorAll(".reveal").forEach(e=>e.setAttribute("data-reveal","in"));return}q=new IntersectionObserver(e=>{for(const t of e)t.isIntersecting&&(t.target.setAttribute("data-reveal","in"),q.unobserve(t.target))},{rootMargin:"0px 0px -10% 0px",threshold:.05}),L(),window.addEventListener("pickprism:reveal-refresh",L)}const R=()=>typeof window<"u"&&window.Pickprism||{};function O(e){let t=e.querySelector(".comment-form__notice");return t||(t=document.createElement("p"),t.className="comment-form__notice",t.setAttribute("role","status"),t.setAttribute("aria-live","polite"),t.hidden=!0,e.insertBefore(t,e.firstChild)),t}function S(e,t,n){const r=O(e);r.className=`comment-form__notice comment-form__notice--${t}`,r.textContent=n,r.hidden=!1}function U(e){const t=e.querySelector(".comment-form__notice");t&&(t.hidden=!0)}function x(e){const t=e.querySelector('input[name="pickprism_ts"]');t&&(t.value=Math.floor(Date.now()/1e3))}function z(){const e=document.getElementById("respond");if(!e)return;const t=document.createElement("span");t.id="respond-placeholder",t.hidden=!0,e.parentNode.insertBefore(t,e),document.addEventListener("click",a=>{const c=a.target.closest("a.comment-reply-link");if(!c)return;a.preventDefault();const o=c.getAttribute("data-commentid")||"0",u=document.getElementById(`comment-${o}`);if(!u)return;let i=e.querySelector('input[name="comment_parent"]');if(!i){i=document.createElement("input"),i.type="hidden",i.name="comment_parent";const d=e.querySelector("form");d&&d.appendChild(i)}i.value=o,u.appendChild(e);const s=e.querySelector("#cancel-comment-reply-link");s&&(s.style.display="",s.addEventListener("click",n,{once:!0}));const l=e.querySelector('textarea[name="comment"]');l&&(l.focus({preventScroll:!0}),l.scrollIntoView({behavior:"smooth",block:"center"}))});function n(a){a.preventDefault();const c=e.querySelector('input[name="comment_parent"]');c&&(c.value="0"),t.parentNode.insertBefore(e,t);const o=e.querySelector("#cancel-comment-reply-link");o&&(o.style.display="none")}const r=e.querySelector("#cancel-comment-reply-link");r&&(r.style.display="none")}async function F(e,t){t.preventDefault();const{restUrl:n,nonce:r,i18n:a={}}=R();if(!n||!r){e.submit();return}const c=e.querySelector(".comment-form__submit");if(c){if(c.getAttribute("aria-busy")==="true")return;c.setAttribute("aria-busy","true"),c.disabled=!0}U(e);const o=new FormData(e),u=new URLSearchParams;o.forEach((i,s)=>u.append(s,typeof i=="string"?i:""));try{const i=await fetch(`${n}comments`,{method:"POST",headers:{"X-WP-Nonce":r,Accept:"application/json","Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},credentials:"same-origin",body:u.toString()}),s=await i.json().catch(()=>null);if(!i.ok||!s){const l=s&&(s.message||s.code)||a.errorGeneric||"Что-то пошло не так.";S(e,"error",l);return}s.status==="approved"&&s.html?(G(e,s),S(e,"success",s.message||"Комментарий опубликован."),A(e)):s.status==="pending"?(S(e,"success",s.message||"Комментарий отправлен и появится после одобрения."),A(e)):S(e,"error",s.message||a.errorGeneric||"Не удалось отправить комментарий.")}catch(i){console.error("[pickprism] comment submit failed",i),S(e,"error",a.errorGeneric||"Сетевая ошибка. Попробуйте ещё раз.")}finally{c&&(c.removeAttribute("aria-busy"),c.disabled=!1)}}function A(e){const t=e.querySelector('textarea[name="comment"]');t&&(t.value="");const n=e.querySelector('input[name="comment_parent"]');n&&(n.value="0"),x(e)}function G(e,t){var u;const n=document.getElementById("comments");if(!n)return;const r=parseInt(((u=e.querySelector('input[name="comment_parent"]'))==null?void 0:u.value)||"0",10);let a=n.querySelector(".comment-list");if(!a){a=document.createElement("ol"),a.className="comment-list";const i=n.querySelector(".comments-title");i?i.after(a):n.prepend(a)}const c=document.createElement("div");c.innerHTML=t.html.trim();const o=c.firstElementChild;if(o){if(r>0){const i=document.getElementById(`comment-${r}`);if(i){let s=i.querySelector(":scope > .children");s||(s=document.createElement("ol"),s.className="children",i.appendChild(s)),s.appendChild(o)}else a.appendChild(o)}else a.appendChild(o);o.scrollIntoView({behavior:"smooth",block:"center"}),o.style.transition="background-color 1.4s ease",o.style.backgroundColor="var(--c-accent-soft)",setTimeout(()=>{o.style.backgroundColor=""},1200)}}function V(){const e=document.getElementById("commentform");e&&(x(e),z(),e.addEventListener("submit",t=>F(e,t)))}const W=()=>typeof window<"u"&&window.matchMedia&&window.matchMedia("(prefers-reduced-motion: reduce)").matches;function X(){const e=document.querySelector("[data-reading-progress]"),t=document.querySelector("[data-reading-progress-bar]"),n=document.querySelector(".pa-article");if(!e||!t||!n)return;let r=!1;const a=()=>{const o=n.getBoundingClientRect(),u=window.innerHeight||0,i=o.height-u;if(i<=0){t.style.width="100%",r=!1;return}const s=Math.max(0,-o.top),l=Math.max(0,Math.min(1,s/i));t.style.width=`${(l*100).toFixed(2)}%`,r=!1},c=()=>{r||(r=!0,W()?a():requestAnimationFrame(a))};a(),window.addEventListener("scroll",c,{passive:!0}),window.addEventListener("resize",c,{passive:!0})}const J=()=>typeof window<"u"&&window.Pickprism||{},h=e=>String(e??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;");function K(e){const t=typeof e.hue=="number"?e.hue:24,n=e.primaryCategory?e.primaryCategory.name:"",r=n?n.charAt(0).toUpperCase():"P";let a="";return e.thumbnail&&e.thumbnail.url?a=`
      <div class="ha-cover">
        <img class="ha-cover__img"
          src="${h(e.thumbnail.url)}"
          ${e.thumbnail.srcset?`srcset="${h(e.thumbnail.srcset)}"`:""}
          ${e.thumbnail.sizes?`sizes="${h(e.thumbnail.sizes)}"`:""}
          width="${h(e.thumbnail.width||800)}"
          height="${h(e.thumbnail.height||500)}"
          loading="lazy"
          decoding="async"
          alt="${h(e.thumbnail.alt||e.title)}"
        />
        ${n?`<span class="ha-cover__cat">${h(n)}</span>`:""}
      </div>
    `:a=`
      <div class="ha-cover" style="--hue: ${t};">
        <div class="ha-cover__bg" aria-hidden="true"></div>
        <div class="ha-cover__letter" aria-hidden="true">${h(r)}</div>
        ${n?`<span class="ha-cover__cat">${h(n)}</span>`:""}
      </div>
    `,`
    <a class="ha-card reveal" href="${h(e.url)}" data-post-id="${h(e.id)}">
      ${a}
      <div class="ha-card__body">
        <div class="ha-card__meta">
          ${e.isNew?'<span class="ha-card__new">Новое</span>':""}
          <span class="ha-card__date"><time datetime="${h(e.dateIso)}">${h(e.date)}</time></span>
          <span class="ha-card__dot" aria-hidden="true">·</span>
          <span class="ha-card__read">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            ${h(e.readTime||1)} мин
          </span>
        </div>
        <h3 class="ha-card__title">${h(e.title)}</h3>
        ${e.excerpt?`<p class="ha-card__excerpt">${h(e.excerpt)}</p>`:""}
      </div>
    </a>
  `}function Q(){const e=document.querySelectorAll("[data-feed-tab]");if(!e.length)return;const t=document.querySelector("[data-feed-container]");if(!t)return;const n=t.querySelector("[data-feed-list]");if(!n)return;const{restUrl:r,feed:a,i18n:c={}}=J();if(!r||!a)return;let o=!1;const u=s=>{e.forEach(l=>{l.classList.remove("is-active"),l.setAttribute("aria-selected","false")}),s.classList.add("is-active"),s.setAttribute("aria-selected","true")},i=(s,l)=>{if(o)return;o=!0,n.setAttribute("aria-busy","true");const d=new URLSearchParams({type:s,value:l||"",paged:"1",per_page:String(a.perPage||12)});fetch(`${r}feed?${d.toString()}`,{credentials:"same-origin",headers:{Accept:"application/json"}}).then(p=>{if(!p.ok)throw new Error(`HTTP ${p.status}`);return p.json()}).then(p=>{const f=p.items||[];n.innerHTML=f.length?f.map(K).join(""):`<p class="empty-state__text">${h(c.noResults||"Ничего не найдено")}</p>`,window.Pickprism&&window.Pickprism.feed&&(window.Pickprism.feed.type=s,window.Pickprism.feed.value=l||"",window.Pickprism.feed.paged=1),window.dispatchEvent(new CustomEvent("pickprism:feed-reset",{detail:{type:s,value:l,hasMore:!!p.hasMore}})),window.dispatchEvent(new CustomEvent("pickprism:reveal-refresh"))}).catch(()=>{n.innerHTML=`<p class="empty-state__text">${h(c.errorGeneric||"Ошибка")}</p>`}).finally(()=>{o=!1,n.removeAttribute("aria-busy")})};e.forEach(s=>{s.addEventListener("click",l=>{l.preventDefault();const d=s.dataset.feedType||"home",p=s.dataset.feedValue||"";u(s),i(d,p)})})}const Y=e=>{document.readyState!=="loading"?e():document.addEventListener("DOMContentLoaded",e,{once:!0})};Y(()=>{document.documentElement.classList.remove("no-js"),document.documentElement.classList.add("has-js"),I(),H(),document.querySelector("[data-feed-container]")&&j(),document.querySelector("[data-feed-tab]")&&Q(),document.querySelector("[data-reading-progress]")&&X(),(document.querySelector(".comment-form")||document.querySelector(".pa-clist"))&&V(),document.querySelectorAll("[data-copy-link]").forEach(e=>{e.addEventListener("click",t=>{t.preventDefault();const n=e.dataset.copyLink||"";!n||!navigator.clipboard||navigator.clipboard.writeText(n).then(()=>{const r=e.getAttribute("aria-label")||"";e.setAttribute("aria-label","Скопировано"),e.style.color="var(--c-accent)",setTimeout(()=>{e.setAttribute("aria-label",r),e.style.color=""},1600)})})})});

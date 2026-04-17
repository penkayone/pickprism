const L=()=>typeof window<"u"&&window.Pickprism||{},o=e=>String(e??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;");function E(e){const t=e.thumbnail?`<span class="search-result__thumb"><img src="${o(e.thumbnail)}" alt="" loading="lazy" decoding="async"></span>`:"";return`
    <a href="${o(e.url)}" class="search-result" role="option">
      ${t}
      <span class="search-result__body">
        <span class="search-result__title">${o(e.title)}</span>
        <span class="search-result__excerpt">${o(e.excerpt||"")}</span>
      </span>
    </a>
  `}function A(e,t,r){const{i18n:n={}}=L();if(!t||!t.items||t.items.length===0){e.innerHTML=`<div class="search-empty">${o(n.noResults||"Ничего не найдено")}</div>`;return}const h=t.items.map(E).join(""),f=t.viewAll?`<a class="search-view-all" href="${o(t.viewAll)}">${o(n.showAll||"Показать все результаты")} →</a>`:"";e.innerHTML=h+f}function M(e){const t=e.querySelector("[data-search-input]"),r=e.querySelector("[data-search-dropdown]");if(!t||!r)return;const{restUrl:n,nonce:h,searchMinLen:f=2,searchDebounce:v=300,i18n:l={}}=L();if(!n)return;let c=null,g=0;const p=()=>{r.hidden=!0,t.setAttribute("aria-expanded","false")},m=()=>{r.hidden=!1,t.setAttribute("aria-expanded","true")},d=i=>{c&&c.abort(),c=new AbortController,r.innerHTML=`<div class="search-loading">${o(l.searching||"Ищем…")}</div>`,m();const w=`${n}search?q=${encodeURIComponent(i)}`,u={Accept:"application/json"};h&&(u["X-WP-Nonce"]=h),fetch(w,{headers:u,credentials:"same-origin",signal:c.signal}).then(a=>{if(!a.ok)throw new Error(`HTTP ${a.status}`);return a.json()}).then(a=>A(r,a)).catch(a=>{a.name!=="AbortError"&&(r.innerHTML=`<div class="search-empty">${o(l.errorGeneric||"Ошибка")}</div>`)})};t.addEventListener("input",()=>{const i=t.value.trim();if(clearTimeout(g),i.length<f){c&&c.abort(),p();return}g=setTimeout(()=>d(i),v)}),t.addEventListener("focus",()=>{t.value.trim().length>=f&&r.innerHTML.trim()&&m()}),document.addEventListener("click",i=>{e.contains(i.target)||p()}),t.addEventListener("keydown",i=>{i.key==="Escape"&&(p(),t.blur())})}function q(){document.querySelectorAll("[data-search]").forEach(M)}const k=()=>typeof window<"u"&&window.Pickprism||{},s=e=>String(e??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;");function x(e){const t=(e.tags||[]).map(n=>`<a class="chip chip--tag chip--sm" href="${s(n.url)}">#${s(n.name)}</a>`).join(""),r=e.thumbnail?`<a class="card__media" href="${s(e.url)}" tabindex="-1" aria-hidden="true">
        <img class="card__img"
          src="${s(e.thumbnail.url)}"
          ${e.thumbnail.srcset?`srcset="${s(e.thumbnail.srcset)}"`:""}
          ${e.thumbnail.sizes?`sizes="${s(e.thumbnail.sizes)}"`:""}
          width="${s(e.thumbnail.width)}"
          height="${s(e.thumbnail.height)}"
          loading="lazy"
          decoding="async"
          alt="${s(e.thumbnail.alt||e.title)}"
        />
      </a>`:"";return`
    <article class="card card--article reveal" data-post-id="${s(e.id)}">
      ${t?`<div class="card__tags">${t}</div>`:""}
      <h2 class="card__title"><a href="${s(e.url)}" rel="bookmark">${s(e.title)}</a></h2>
      ${r}
      <div class="card__excerpt">${s(e.excerpt||"")}</div>
      <div class="card__meta">
        <time datetime="${s(e.dateIso)}">${s(e.date)}</time>
      </div>
    </article>
  `}function T(){const e=document.querySelector("[data-feed-container]");if(!e)return;const t=e.querySelector("[data-feed-list]"),r=e.querySelector("[data-feed-sentinel]"),n=e.querySelector("[data-feed-load-more]"),h=e.querySelector("[data-feed-status]"),f=e.querySelector(".pagination__links");if(!t)return;const{restUrl:v,feed:l,i18n:c={}}=k();if(!v||!l)return;let g=(l.paged||1)+1,p=!1,m=!1,d=null;f&&(f.hidden=!0),r&&(r.hidden=!1),n&&(n.hidden=!1);const i=u=>{h&&(h.textContent=u||"")},w=()=>{if(p||m)return;p=!0,i(c.loading||"Загружаем…"),n&&(n.disabled=!0);const u=new URLSearchParams({type:l.type||"home",value:l.value||"",paged:String(g),per_page:String(l.perPage||10)});fetch(`${v}feed?${u.toString()}`,{credentials:"same-origin",headers:{Accept:"application/json"}}).then(a=>{if(!a.ok)throw new Error(`HTTP ${a.status}`);return a.json()}).then(a=>{const y=a.items||[];if(y.length===0&&!a.hasMore){m=!0,i(c.endOfFeed||"Это все статьи"),n&&(n.hidden=!0),d&&r&&d.unobserve(r);return}const _=document.createDocumentFragment(),$=document.createElement("div");for($.innerHTML=y.map(x).join("");$.firstChild;)_.appendChild($.firstChild);t.appendChild(_),window.dispatchEvent(new CustomEvent("pickprism:reveal-refresh")),g+=1,a.hasMore?i(""):(m=!0,i(c.endOfFeed||"Это все статьи"),n&&(n.hidden=!0),d&&r&&d.unobserve(r))}).catch(()=>{i(c.errorGeneric||"Ошибка")}).finally(()=>{p=!1,n&&(n.disabled=!1)})};n&&n.addEventListener("click",w),r&&"IntersectionObserver"in window&&(d=new IntersectionObserver(u=>{for(const a of u)a.isIntersecting&&w()},{rootMargin:"400px 0px"}),d.observe(r))}const I=()=>typeof window<"u"&&window.matchMedia&&window.matchMedia("(prefers-reduced-motion: reduce)").matches;let b=null;function S(){b&&document.querySelectorAll(".reveal:not([data-reveal])").forEach(e=>{b.observe(e)})}function j(){if(I()||!("IntersectionObserver"in window)){document.querySelectorAll(".reveal").forEach(e=>e.setAttribute("data-reveal","in"));return}b=new IntersectionObserver(e=>{for(const t of e)t.isIntersecting&&(t.target.setAttribute("data-reveal","in"),b.unobserve(t.target))},{rootMargin:"0px 0px -10% 0px",threshold:.05}),S(),window.addEventListener("pickprism:reveal-refresh",S)}const C=e=>{document.readyState!=="loading"?e():document.addEventListener("DOMContentLoaded",e,{once:!0})};C(()=>{document.documentElement.classList.remove("no-js"),document.documentElement.classList.add("has-js"),q(),T(),j()});

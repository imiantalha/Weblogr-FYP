document.addEventListener('DOMContentLoaded', () => {
  const grid=document.querySelector('[data-public-feed]');
  const sentinel=document.querySelector('[data-feed-sentinel]');
  if(!grid||!sentinel) return;
  let nextPage=Number(grid.dataset.nextPage||0), loading=false;
  const status=document.createElement('p'); status.className='feed-status'; status.setAttribute('aria-live','polite'); sentinel.appendChild(status);
  const params=new URLSearchParams({per_page:grid.dataset.perPage||'12'});
  const search=grid.dataset.search||'', category=grid.dataset.category||'';
  if(search) params.set('search',search); if(category) params.set('category',category);
  const load=async()=>{
    if(loading||!nextPage) return; loading=true; status.textContent='Loading more stories…';
    try{
      params.set('page',String(nextPage));
      const response=await fetch(`api/public-posts.php?${params.toString()}`,{headers:{Accept:'application/json'},credentials:'same-origin'});
      if(!response.ok) throw new Error('Request failed');
      const data=await response.json();
      data.items.forEach(html=>grid.insertAdjacentHTML('beforeend',html));
      nextPage=data.next_page||0; grid.dataset.nextPage=String(nextPage);
      status.textContent=nextPage?'Scroll for more stories.':'';
      if(!nextPage) sentinel.remove();
    }catch(error){ status.textContent='Could not load more stories. Please try again.'; }
    finally{loading=false;}
  };
  if('IntersectionObserver' in window){
    const observer=new IntersectionObserver(entries=>{if(entries.some(entry=>entry.isIntersecting)) load();},{rootMargin:'600px 0px'}); observer.observe(sentinel);
  } else { const button=document.createElement('button'); button.className='button button-secondary'; button.type='button'; button.textContent='Load more'; button.addEventListener('click',load); sentinel.replaceChildren(button); }
});

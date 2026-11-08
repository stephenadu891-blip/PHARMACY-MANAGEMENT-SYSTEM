document.querySelectorAll("[data-confirm]").forEach(el=>{
  el.addEventListener("click",ev=>{
    if(!confirm(el.getAttribute("data-confirm") || "Are you sure?")) ev.preventDefault();
  });
});

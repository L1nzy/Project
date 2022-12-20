let $search = document.querySelector('.search-block-form');
let $searchPopup = document.querySelector('#search-block-form');

$search.addEventListener('click',()=>{
  $searchPopup.classList.toggle('active');
})

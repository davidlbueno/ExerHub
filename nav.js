// Define separate arrays for sideNav and topNav items
const sideNavItems = [
  { title: 'Method', href: '#' },
  { title: 'Progressions', href: '#' },
  { title: 'Exercises', href: 'exercises.php' },
  { title: 'Workouts', href: 'workouts.php' },
];

const topNavItems = [
  { title: 'Log In', href: '#' },
  { title: 'Create Account', href: '#' },
];

document.addEventListener('DOMContentLoaded', function () {
  var elems = document.querySelectorAll('.sidenav');
  var instances = M.Sidenav.init(elems);

  // Generate navigation links
  const topNav = document.querySelector('#top-nav');
  const sideNav = document.querySelector('#side-nav');

  // Add items to sideNav
  sideNavItems.forEach((item) => {
    const li = document.createElement('li');
    const a = document.createElement('a');

    a.textContent = item.title;
    a.href = item.href;

    li.appendChild(a);
    sideNav.appendChild(li.cloneNode(true));
  });

  // Add items to topNav
  topNavItems.forEach((item) => {
    const li = document.createElement('li');
    const a = document.createElement('a');

    a.textContent = item.title;
    a.href = item.href;

    li.appendChild(a);
    topNav.appendChild(li.cloneNode(true));
  });

  // Make top-nav always visible
  topNav.classList.remove('hide');
});

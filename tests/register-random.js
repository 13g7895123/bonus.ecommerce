// 貼入瀏覽器 DevTools Console，自動填入註冊表單

(async () => {
  const rand = (arr) => arr[Math.floor(Math.random() * arr.length)];
  const randInt = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;
  const randStr = (n, c = 'abcdefghijklmnopqrstuvwxyz') =>
    Array.from({ length: n }, () => c[randInt(0, c.length - 1)]).join('');

  const email     = `${randStr(randInt(5, 9))}${randInt(10,99)}@${randStr(randInt(4,7))}.${rand(['com','net','io','tw'])}`;
  const firstName = rand(['James','Emma','Liam','Olivia','Noah','Ava','Lucas','Mia','Ethan','Chloe']);
  const lastName  = rand(['Wang','Chen','Lin','Lee','Wu','Liu','Huang','Yang','Chang','Cheng']);
  const password  = `Test${randInt(1000,9999)}!`;
  const year      = randInt(1975, 2000);
  const month     = String(randInt(1, 12)).padStart(2, '0');
  const day       = String(randInt(1, 28)).padStart(2, '0');
  const dob       = `${year}-${month}-${day}`;
  const phone     = `09${Array.from({length:8}, () => randInt(0,9)).join('')}`;

  function fill(el, value) {
    if (!el) return;
    Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')
      .set.call(el, value);
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function fillCheckbox(el, checked) {
    if (!el) return;
    Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'checked')
      .set.call(el, checked);
    el.dispatchEvent(new Event('change', { bubbles: true }));
  }

  fill(document.querySelector('input[placeholder="電子郵件"]'),          email);
  fill(document.querySelector('input[placeholder="名字 (First Name)"]'), firstName);
  fill(document.querySelector('input[placeholder="姓氏 (Last Name)"]'),  lastName);
  fill(document.querySelector('input[type="password"]'),                 password);
  fill(document.querySelector('input[type="date"]'),                     dob);
  fill(document.querySelector('input.phone-number-input'),               phone);
  fillCheckbox(document.querySelector('input[type="checkbox"]#terms'),   true);

  console.log('✅ 已填入：', { email, firstName, lastName, password, dob, phone });
})();

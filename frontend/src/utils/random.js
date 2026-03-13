// src/utils/random.js

export function getRandomString(length) {
  const characters = 'abcdefghijklmnopqrstuvwxyz';
  let result = '';
  for (let i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * characters.length));
  }
  return result;
}

export function getRandomNumber(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

export function getRandomEmail() {
  return `${getRandomString(8)}@example.com`;
}

export function getRandomPhone() {
  return `09${getRandomNumber(10000000, 99999999)}`;
}

export function getRandomDate() {
  const start = new Date(1970, 0, 1);
  const end = new Date(2005, 0, 1);
  const date = new Date(start.getTime() + Math.random() * (end.getTime() - start.getTime()));
  return date.toISOString().split('T')[0];
}

export function getRandomName() {
  const names = ['John', 'Jane', 'Alice', 'Bob', 'Charlie', 'Eve', 'David', 'Frank', 'Grace', 'Heidi'];
  return names[Math.floor(Math.random() * names.length)];
}

export function getRandomSurname() {
  const surnames = ['Doe', 'Smith', 'Johnson', 'Brown', 'Davis', 'Wilson', 'Evans', 'Thomas', 'Roberts'];
  return surnames[Math.floor(Math.random() * surnames.length)];
}

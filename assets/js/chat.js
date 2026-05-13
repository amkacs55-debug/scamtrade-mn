// Chat — auto-scroll & soft-poll feel
document.addEventListener('DOMContentLoaded', () => {
  const box = document.querySelector('.chat-msgs');
  if (box) box.scrollTop = box.scrollHeight;
});


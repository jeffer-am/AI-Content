(function () {
  const frame_container = document.createElement("div");
  frame_container.style.display = "flex";
  frame_container.style.justifyContent = "center";
  frame_container.style.alignItems = "center";
  frame_container.style.padding = "10px";

  const frame = document.createElement("iframe");
  frame.src = "https://wpaimuse.com/campaign";
  frame.height = "120px";
  frame.style.border = "none";
  frame.style.width = "75%";

  frame_container.appendChild(frame);

  const container = document.querySelector("#fs_pricing");

  console.log(container);

  if (!container) return;

  container.prepend(frame_container);
})();

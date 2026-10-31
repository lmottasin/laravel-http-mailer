"""Build README images from art/src/*.html with headless Chrome.

Each template is rendered twice:
  <name>.png         desktop, 1280px wide
  <name>-mobile.png  mobile, 480px wide (templates switch layout at max-width: 700px)
src/<name>-animated.svg becomes <name>.svg (icons inlined, no other processing).

Icons: write {{icon:NAME:HEXCOLOR:SIZE}} in a template; NAME is a file in art/icons/ (simple-icons).
Usage: python3 art/build.py [name ...]
"""
import base64, pathlib, re, subprocess, sys

ART = pathlib.Path(__file__).resolve().parent
CHROME = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
BG = "#f4f4f1"
FIXED_HEIGHT = {"hero": 640, "social": 640}  # 2:1 images
DESKTOP_ONLY = {"social"}  # GitHub social preview: no mobile variant
VARIANTS = {"": (1280, 64), "-mobile": (480, 40)}  # suffix: (css width, trimmed border in image px)


def icon(m):
    name, color, size = m.groups()
    d = re.search(r'd="([^"]+)"', (ART / "icons" / f"{name}.svg").read_text()).group(1)
    return f'<svg width="{size}" height="{size}" viewBox="0 0 24 24" aria-hidden="true"><path fill="#{color}" d="{d}"/></svg>'


def render(name):
    html = re.sub(r"\{\{icon:([a-z]+):([0-9a-fA-F]{6}):(\d+)\}\}", icon, (ART / "src" / f"{name}.html").read_text())
    built = ART / f".{name}.built.html"
    built.write_text(html)
    try:
        for suffix, (width, border) in VARIANTS.items():
            if suffix and name in DESKTOP_ONLY:
                continue
            png = ART / f"{name}{suffix}.png"
            fixed = FIXED_HEIGHT.get(name) if suffix == "" else None
            subprocess.run([CHROME, "--headless=new", "--disable-gpu", "--hide-scrollbars",
                            "--force-device-scale-factor=2", f"--window-size={width},{fixed or 3000}",
                            "--virtual-time-budget=4000", f"--screenshot={png}", built.as_uri()],
                           check=True, capture_output=True)
            post = ["-strip"] if fixed else ["-fuzz", "1%", "-trim", "+repage", "-bordercolor", BG, "-border", str(border), "-strip"]
            subprocess.run(["magick", str(png), *post, "-define", "png:compression-level=9", str(png)], check=True)
            print(f"{png.name:32} {png.stat().st_size // 1024} KB")
    finally:
        built.unlink()


def render_svg(src):
    """src/<name>-animated.svg -> <name>.svg with icons inlined (animated SVGs play inside GitHub READMEs)."""
    out = ART / (src.stem.removesuffix("-animated") + ".svg")
    svg = re.sub(r"\{\{icon:([a-z]+):([0-9a-fA-F]{6}):(\d+)\}\}", icon, src.read_text())
    # GitHub shows README SVGs as <img>, which can't load other files, so the avatar is embedded.
    avatar = base64.b64encode((ART / "avatar-96.png").read_bytes()).decode()
    out.write_text(svg.replace("{{avatar}}", f"data:image/png;base64,{avatar}"))
    print(f"{out.name:32} {out.stat().st_size // 1024} KB")


names = sys.argv[1:] or [p.stem for p in sorted((ART / "src").glob("*.html"))]
for n in names:
    render(n)
for svg in sorted((ART / "src").glob("*-animated.svg")):
    if not sys.argv[1:] or svg.stem.removesuffix("-animated") in names:
        render_svg(svg)

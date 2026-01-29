import pathlib
import requests

BASE_DIR = pathlib.Path(r"C:\wamp64\www\proyectosgemini\PrimerPasoDigital\Web\WebClientes\Mauro Rivero - Asoc Coop Global Indruprod\globalinduprod\servicios")

DOWNLOADS = {
    "hero/page": [
        ("hero/services-hero.jpg", "https://images.pexels.com/photos/386143/pexels-photo-386143.jpeg?auto=compress&cs=tinysrgb&w=1800"),
    ],
    "construccion-montaje": {
        "thumbnail": "https://images.pexels.com/photos/585419/pexels-photo-585419.jpeg?auto=compress&cs=tinysrgb&w=900",
        "hero": "https://images.pexels.com/photos/159306/construction-site-build-construction-work-159306.jpeg?auto=compress&cs=tinysrgb&w=1800",
        "gallery": [
            "https://images.pexels.com/photos/809016/pexels-photo-809016.jpeg?auto=compress&cs=tinysrgb&w=1600",
            "https://images.pexels.com/photos/1108101/pexels-photo-1108101.jpeg?auto=compress&cs=tinysrgb&w=1600",
        ],
    },
    "mantenimiento-industrial": {
        "thumbnail": "https://images.pexels.com/photos/373782/pexels-photo-373782.jpeg?auto=compress&cs=tinysrgb&w=900",
        "hero": "https://images.pexels.com/photos/256381/pexels-photo-256381.jpeg?auto=compress&cs=tinysrgb&w=1800",
        "gallery": [
            "https://images.pexels.com/photos/3739203/pexels-photo-3739203.jpeg?auto=compress&cs=tinysrgb&w=1600",
            "https://images.pexels.com/photos/4254168/pexels-photo-4254168.jpeg?auto=compress&cs=tinysrgb&w=1600",
        ],
    },
    "servicios-portuarios": {
        "thumbnail": "https://images.pexels.com/photos/306347/pexels-photo-306347.jpeg?auto=compress&cs=tinysrgb&w=900",
        "hero": "https://images.pexels.com/photos/417054/pexels-photo-417054.jpeg?auto=compress&cs=tinysrgb&w=1800",
        "gallery": [
            "https://images.pexels.com/photos/2219024/pexels-photo-2219024.jpeg?auto=compress&cs=tinysrgb&w=1600",
            "https://images.pexels.com/photos/327272/pexels-photo-327272.jpeg?auto=compress&cs=tinysrgb&w=1600",
        ],
    },
    "logistica-suministros": {
        "thumbnail": "https://images.pexels.com/photos/4483610/pexels-photo-4483610.jpeg?auto=compress&cs=tinysrgb&w=900",
        "hero": "https://images.pexels.com/photos/256219/pexels-photo-256219.jpeg?auto=compress&cs=tinysrgb&w=1800",
        "gallery": [
            "https://images.pexels.com/photos/279642/pexels-photo-279642.jpeg?auto=compress&cs=tinysrgb&w=1600",
            "https://images.pexels.com/photos/3057960/pexels-photo-3057960.jpeg?auto=compress&cs=tinysrgb&w=1600",
        ],
    },
    "importacion-comercializacion": {
        "thumbnail": "https://images.pexels.com/photos/11576461/pexels-photo-11576461.jpeg?auto=compress&cs=tinysrgb&w=900",
        "hero": "https://images.pexels.com/photos/1554647/pexels-photo-1554647.jpeg?auto=compress&cs=tinysrgb&w=1800",
        "gallery": [
            "https://images.pexels.com/photos/3183198/pexels-photo-3183198.jpeg?auto=compress&cs=tinysrgb&w=1600",
            "https://images.pexels.com/photos/1554643/pexels-photo-1554643.jpeg?auto=compress&cs=tinysrgb&w=1600",
        ],
    },
    "gestion-residuos": {
        "thumbnail": "https://images.pexels.com/photos/4680551/pexels-photo-4680551.jpeg?auto=compress&cs=tinysrgb&w=900",
        "hero": "https://images.pexels.com/photos/4680550/pexels-photo-4680550.jpeg?auto=compress&cs=tinysrgb&w=1800",
        "gallery": [
            "https://images.pexels.com/photos/4680552/pexels-photo-4680552.jpeg?auto=compress&cs=tinysrgb&w=1600",
            "https://images.pexels.com/photos/4680553/pexels-photo-4680553.jpeg?auto=compress&cs=tinysrgb&w=1600",
        ],
    },
    "alquiler-equipos": {
        "thumbnail": "https://images.pexels.com/photos/459728/pexels-photo-459728.jpeg?auto=compress&cs=tinysrgb&w=900",
        "hero": "https://images.pexels.com/photos/280014/pexels-photo-280014.jpeg?auto=compress&cs=tinysrgb&w=1800",
        "gallery": [
            "https://images.pexels.com/photos/236698/pexels-photo-236698.jpeg?auto=compress&cs=tinysrgb&w=1600",
            "https://images.pexels.com/photos/439416/pexels-photo-439416.jpeg?auto=compress&cs=tinysrgb&w=1600",
        ],
    },
    "capital-humano": {
        "thumbnail": "https://images.pexels.com/photos/3184312/pexels-photo-3184312.jpeg?auto=compress&cs=tinysrgb&w=900",
        "hero": "https://images.pexels.com/photos/3184360/pexels-photo-3184360.jpeg?auto=compress&cs=tinysrgb&w=1800",
        "gallery": [
            "https://images.pexels.com/photos/3184418/pexels-photo-3184418.jpeg?auto=compress&cs=tinysrgb&w=1600",
            "https://images.pexels.com/photos/3184420/pexels-photo-3184420.jpeg?auto=compress&cs=tinysrgb&w=1600",
        ],
    },
    "seguridad-industrial": {
        "thumbnail": "https://images.pexels.com/photos/3855961/pexels-photo-3855961.jpeg?auto=compress&cs=tinysrgb&w=900",
        "hero": "https://images.pexels.com/photos/3855962/pexels-photo-3855962.jpeg?auto=compress&cs=tinysrgb&w=1800",
        "gallery": [
            "https://images.pexels.com/photos/3855963/pexels-photo-3855963.jpeg?auto=compress&cs=tinysrgb&w=1600",
            "https://images.pexels.com/photos/3855976/pexels-photo-3855976.jpeg?auto=compress&cs=tinysrgb&w=1600",
        ],
    },
}

session = requests.Session()
session.headers.update({"User-Agent": "Mozilla/5.0"})

for relative_path, url in DOWNLOADS["hero/page"]:
    destination = BASE_DIR / relative_path
    destination.parent.mkdir(parents=True, exist_ok=True)
    response = session.get(url, timeout=60)
    response.raise_for_status()
    destination.write_bytes(response.content)

for service, assets in DOWNLOADS.items():
    if service == "hero/page":
        continue
    service_dir = BASE_DIR / service
    service_dir.mkdir(parents=True, exist_ok=True)

    thumb_path = service_dir / "thumbnail.jpg"
    hero_path = service_dir / "hero.jpg"

    response = session.get(assets["thumbnail"], timeout=60)
    response.raise_for_status()
    thumb_path.write_bytes(response.content)

    response = session.get(assets["hero"], timeout=60)
    response.raise_for_status()
    hero_path.write_bytes(response.content)

    gallery_dir = service_dir / "gallery"
    gallery_dir.mkdir(parents=True, exist_ok=True)

    for index, url in enumerate(assets["gallery"], start=1):
        gallery_path = gallery_dir / f"gallery-{index:02}.jpg"
        response = session.get(url, timeout=60)
        response.raise_for_status()
        gallery_path.write_bytes(response.content)

print("Descargas completadas.")

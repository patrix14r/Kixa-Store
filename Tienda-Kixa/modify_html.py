import os
import glob
import re

files = ["index.html", "hombre.html", "mujer.html", "niños.html", "deporte.html", "producto.html"]

menu_html = """    <nav class="nav-links main-menu">
      <div class="nav-item">
        <a href="mujer.html">Mujer</a>
        <div class="mega-menu">
          <a href="mujer.html#running">Running</a>
          <a href="mujer.html#gym">Gym/Fitness</a>
          <a href="mujer.html#urbano">Urbano</a>
          <a href="mujer.html#plataformas">Plataformas</a>
          <a href="mujer.html#accesorios">Accesorios</a>
        </div>
      </div>
      <div class="nav-item">
        <a href="hombre.html">Hombre</a>
        <div class="mega-menu">
          <a href="hombre.html#running">Running</a>
          <a href="hombre.html#futbol">Fútbol</a>
          <a href="hombre.html#urbano">Urbano</a>
          <a href="hombre.html#training">Training</a>
          <a href="hombre.html#basquet">Básquet</a>
        </div>
      </div>
      <div class="nav-item">
        <a href="niños.html">Niños</a>
        <div class="mega-menu">
          <a href="niños.html#escolar">Calzado Escolar</a>
          <a href="niños.html#deportivas">Zapatillas Deportivas</a>
          <a href="niños.html#bebes">Bebés/Primeros Pasos</a>
        </div>
      </div>
      <div class="nav-item">
        <a href="deporte.html">Deporte</a>
        <div class="mega-menu">
          <a href="deporte.html#futbol">Fútbol (Grass/Futsal)</a>
          <a href="deporte.html#running">Running Pro</a>
          <a href="deporte.html#training">Training</a>
        </div>
      </div>
    </nav>"""

mobile_drawer = """
  <!-- Mobile Drawer -->
  <div class="mobile-drawer">
    <button class="close-drawer-btn"><i class="fa fa-times"></i></button>
    <div class="mobile-drawer-content">
      <div class="drawer-item">
        <a href="mujer.html">Mujer</a>
        <div class="drawer-sub">
          <a href="mujer.html#running">Running</a>
          <a href="mujer.html#gym">Gym/Fitness</a>
          <a href="mujer.html#urbano">Urbano</a>
          <a href="mujer.html#plataformas">Plataformas</a>
          <a href="mujer.html#accesorios">Accesorios</a>
        </div>
      </div>
      <div class="drawer-item">
        <a href="hombre.html">Hombre</a>
        <div class="drawer-sub">
          <a href="hombre.html#running">Running</a>
          <a href="hombre.html#futbol">Fútbol</a>
          <a href="hombre.html#urbano">Urbano</a>
          <a href="hombre.html#training">Training</a>
          <a href="hombre.html#basquet">Básquet</a>
        </div>
      </div>
      <div class="drawer-item">
        <a href="niños.html">Niños</a>
        <div class="drawer-sub">
          <a href="niños.html#escolar">Calzado Escolar</a>
          <a href="niños.html#deportivas">Zapatillas Deportivas</a>
          <a href="niños.html#bebes">Bebés/Primeros Pasos</a>
        </div>
      </div>
      <div class="drawer-item">
        <a href="deporte.html">Deporte</a>
        <div class="drawer-sub">
          <a href="deporte.html#futbol">Fútbol (Grass/Futsal)</a>
          <a href="deporte.html#running">Running Pro</a>
          <a href="deporte.html#training">Training</a>
        </div>
      </div>
    </div>
  </div>
"""

for f in files:
    with open(f, 'r', encoding='utf-8') as file:
        content = file.read()
    
    # Replace nav-links
    content = re.sub(r'<nav class="nav-links.*?>.*?</nav>', menu_html, content, flags=re.DOTALL)
    
    # Add mobile button in icons/header
    if '<button class="mobile-menu-btn">' not in content:
        content = re.sub(r'(<div class="icons">)', r'<button class="mobile-menu-btn"><i class="fa fa-bars"></i></button>\n    \1', content)
        
    # Add mobile drawer before </body>
    if 'class="mobile-drawer"' not in content:
        content = content.replace('</body>', mobile_drawer + '\n</body>')
        
    with open(f, 'w', encoding='utf-8') as file:
        file.write(content)

print("Done")

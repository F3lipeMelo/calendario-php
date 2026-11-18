from pathlib import Path
from math import hypot
from PIL import Image, ImageDraw, ImageFont
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_CELL_VERTICAL_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn

ROOT = Path(__file__).resolve().parents[1]
DOCS = ROOT / "docs"
DIAGRAMS = DOCS / "diagramas"
DIAGRAMS.mkdir(parents=True, exist_ok=True)
FONT = "C:/Windows/Fonts/arial.ttf"
FONT_BOLD = "C:/Windows/Fonts/arialbd.ttf"
INK, BLUE, PALE, GRAY = "#1D2635", "#213E63", "#E9F0F8", "#66758A"

def font(size, bold=False):
    return ImageFont.truetype(FONT_BOLD if bold else FONT, size)

def text(draw, xy, value, size=22, color=INK, bold=False, anchor=None):
    draw.text(xy, value, font=font(size, bold), fill=color, anchor=anchor)

def actor(draw, x, y, label):
    draw.ellipse((x-18,y-83,x+18,y-47), outline=BLUE, width=4)
    draw.line((x,y-47,x,y+28), fill=BLUE, width=4)
    draw.line((x-43,y-20,x+43,y-20), fill=BLUE, width=4)
    draw.line((x,y+28,x-35,y+81), fill=BLUE, width=4)
    draw.line((x,y+28,x+35,y+81), fill=BLUE, width=4)
    text(draw,(x,y+100),label,21,anchor="mm")

def ellipse(draw, box, label, planned=False):
    draw.ellipse(box, outline=GRAY if planned else BLUE, width=3, fill="#FFFFFF")
    cx=(box[0]+box[2])//2; cy=(box[1]+box[3])//2
    text(draw,(cx,cy),label,20,GRAY if planned else INK,bold=True,anchor="mm")

def include_arrow(draw, start, end):
    x1, y1 = start; x2, y2 = end
    length = hypot(x2 - x1, y2 - y1)
    ux, uy = (x2 - x1) / length, (y2 - y1) / length
    distance = 0
    while distance < length - 18:
        stop = min(distance + 12, length - 18)
        draw.line((x1 + ux*distance, y1 + uy*distance,
                   x1 + ux*stop, y1 + uy*stop), fill=GRAY, width=3)
        distance += 21
    draw.line((x2 - ux*18, y2 - uy*18, x2, y2), fill=GRAY, width=3)
    draw.line((x2 - ux*16 - uy*8, y2 - uy*16 + ux*8, x2, y2), fill=GRAY, width=3)
    draw.line((x2 - ux*16 + uy*8, y2 - uy*16 - ux*8, x2, y2), fill=GRAY, width=3)

def use_case_diagram():
    im=Image.new("RGB",(1500,1000),"white"); d=ImageDraw.Draw(im)
    d.rounded_rectangle((270,50,1240,930),radius=18,outline=BLUE,width=4)
    text(d,(755,75),"Hoshi Calendar",30,BLUE,True,anchor="mt")
    actor(d,130,425,"Usuário")
    actor(d,1370,625,"Serviço externo")
    boxes=[
        (350,170,705,255,"Criar marcação",False),
        (350,305,705,390,"Consultar calendário",False),
        (350,440,705,525,"Editar marcação",False),
        (350,575,705,660,"Concluir ou reabrir",False),
        (350,710,705,795,"Excluir marcação",False),
        (790,250,1150,335,"Validar período",False),
        (790,575,1150,660,"Enviar lembrete",True),
    ]
    for x1,y1,x2,y2,label,planned in boxes: ellipse(d,(x1,y1,x2,y2),label,planned)
    for yy in [212,347,482,617,752]: d.line((168,425,350,yy),fill=BLUE,width=3)
    include_arrow(d,(705,212),(790,292))
    include_arrow(d,(705,482),(790,292))
    text(d,(711,180),"<<include>>",16,GRAY)
    text(d,(711,390),"<<include>>",16,GRAY)
    d.line((1150,617,1330,625),fill=GRAY,width=3)
    text(d,(805,735),"Cinza: funcionalidade planejada",19,GRAY)
    path=DIAGRAMS/"casos-de-uso.png"; im.save(path); return path

def class_box(d, box, name, attrs, methods, planned=False):
    x1,y1,x2,y2=box
    d.rounded_rectangle(box,radius=9,outline=GRAY if planned else BLUE,width=3,fill="#FFFFFF")
    d.rectangle((x1+2,y1+2,x2-2,y1+48),fill=PALE)
    d.line((x1,y1+48,x2,y1+48),fill=BLUE,width=2)
    name_font=21 if len(name)<20 else 19
    text(d,((x1+x2)//2,y1+24),name,name_font,BLUE,True,anchor="mm")
    yy=y1+61
    for attr in attrs:
        text(d,(x1+16,yy),attr,18); yy+=27
    d.line((x1,yy+2,x2,yy+2),fill=BLUE,width=2); yy+=15
    for method in methods:
        text(d,(x1+16,yy),method,18); yy+=27
    if planned: text(d,(x2-13,y2-12),"planejada",16,GRAY,anchor="rs")

def diamond(d,x,y):
    d.polygon([(x,y),(x-12,y+15),(x,y+30),(x+12,y+15)],outline=BLUE,fill="white")

def class_diagram():
    im=Image.new("RGB",(1800,1340),"white"); d=ImageDraw.Draw(im)
    class_box(d,(60,60,565,335),"Usuario",["- id: int","- nome: String"],["+ criarMarcacao(dados: Dados): Marcacao","+ consultar(inicio: Date, fim: Date): List"],True)
    class_box(d,(715,60,1290,335),"Calendario",["- id: int","- nome: String"],["+ listar(inicio: Date, fim: Date): List","+ marcacoesEm(data: Date): List"],True)
    class_box(d,(715,455,1705,875),"Marcacao",["- id: int","- titulo: String","- descricao: String","- inicio: Date","- fim: Date?","- hora: Time","- tipo: TipoMarcacao","- concluida: Boolean"],["+ validarPeriodo(): Boolean","+ concluir(): void","+ reabrir(): void"])
    class_box(d,(60,460,565,760),"TipoMarcacao",["TAREFA, COMPROMISSO, PRAZO","LEMBRETE, EVENTO, OUTRO"],["+ rotulo(): String"],True)
    class_box(d,(60,970,665,1260),"Notificacao",["- id: int","- antecedenciaMin: int","- enviadaEm: DateTime?"],["+ agendar(): void","+ cancelar(): void"],True)
    class_box(d,(1040,970,1710,1260),"ServicoNotificacao",["<<interface>>"],["+ enviar(destino: String, mensagem: String): Boolean"],True)
    d.line((565,200,715,200),fill=BLUE,width=3);text(d,(590,175),"1",18);text(d,(680,175),"1",18)
    d.line((1000,335,1000,455),fill=BLUE,width=3);diamond(d,1000,345);text(d,(1020,355),"1",18);text(d,(1020,430),"0..*",18)
    d.line((565,620,715,620),fill=BLUE,width=3);text(d,(584,592),"1",18);text(d,(684,592),"1",18)
    d.line((715,780,665,1080),fill=GRAY,width=3);diamond(d,707,800);text(d,(677,818),"1",18);text(d,(650,1025),"0..*",18)
    d.line((665,1100,1040,1100),fill=GRAY,width=3);text(d,(680,1070),"0..*",18);text(d,(1010,1070),"1",18)
    path=DIAGRAMS/"classes-projeto.png";im.save(path);return path

def shade(cell, fill):
    tcPr=cell._tc.get_or_add_tcPr(); shd=OxmlElement("w:shd");shd.set(qn("w:fill"),fill);tcPr.append(shd)

def borders(cell):
    tcPr=cell._tc.get_or_add_tcPr(); b=OxmlElement("w:tcBorders")
    for side in ["top","left","bottom","right"]:
        x=OxmlElement("w:"+side);x.set(qn("w:val"),"single");x.set(qn("w:color"),"D9D9D9");x.set(qn("w:sz"),"4");b.append(x)
    tcPr.append(b)

def table(doc, heads, rows, widths):
    t=doc.add_table(rows=1,cols=len(heads));t.alignment=WD_TABLE_ALIGNMENT.CENTER
    for j,h in enumerate(heads):
        c=t.rows[0].cells[j];c.text=h;shade(c,"213E63");borders(c)
        for run in c.paragraphs[0].runs: run.font.color.rgb=RGBColor(255,255,255);run.bold=True
    for i,row in enumerate(rows):
        cells=t.add_row().cells
        for j,val in enumerate(row):
            cells[j].text=str(val);borders(cells[j]);cells[j].vertical_alignment=WD_CELL_VERTICAL_ALIGNMENT.CENTER
            if i%2: shade(cells[j],"F3F6FA")
    for row in t.rows:
        for j,c in enumerate(row.cells):
            c.width=Inches(widths[j])
            for p in c.paragraphs:
                p.paragraph_format.space_after=Pt(2)
                for run in p.runs: run.font.size=Pt(9)
    doc.add_paragraph()
    return t

def add_case(doc, code, title, actor, pre, main, alternate, exception, post):
    doc.add_heading(f"{code} {title}",level=2)
    for label,value in [("Ator",actor),("Pré condições",pre),("Fluxo principal",main),("Fluxo alternativo",alternate),("Exceções",exception),("Pós condições",post)]:
        p=doc.add_paragraph();p.add_run(label+"  ").bold=True;p.add_run(value)

def build():
    use=use_case_diagram(); classes=class_diagram()
    doc=Document(); sec=doc.sections[0];sec.page_width=Inches(8.5);sec.page_height=Inches(11)
    sec.top_margin=Inches(.72);sec.bottom_margin=Inches(.68);sec.left_margin=Inches(.78);sec.right_margin=Inches(.78)
    styles=doc.styles
    for name in ["Normal","Title","Heading 1","Heading 2"]:
        style=styles[name];style.font.name="Arial";style.font.color.rgb=RGBColor(0,0,0)
    styles["Normal"].font.size=Pt(10.5);styles["Normal"].paragraph_format.space_after=Pt(7)
    styles["Title"].font.size=Pt(25);styles["Heading 1"].font.size=Pt(15);styles["Heading 2"].font.size=Pt(12)
    styles["Heading 1"].paragraph_format.space_before=Pt(15);styles["Heading 1"].paragraph_format.space_after=Pt(7)
    styles["Heading 2"].paragraph_format.space_before=Pt(11);styles["Heading 2"].paragraph_format.space_after=Pt(5)
    p=doc.add_paragraph(style="Title");p.add_run("Documentação de análise e projeto do Hoshi Calendar")
    doc.add_paragraph("Trabalho prático de Análise e Projeto de Sistemas")
    doc.add_paragraph("Integrante  Felipe Melo dos Santos")
    doc.add_paragraph("Data  18 de novembro de 2026")
    doc.add_paragraph("Repositório  https://github.com/F3lipeMelo/calendario-php")
    doc.add_paragraph("Este documento apresenta o escopo, os requisitos, os casos de uso e o modelo de classes do calendário. A implementação inicial já permite gerenciar marcações de vários tipos e períodos; o envio automático de lembretes permanece previsto para uma etapa futura.")
    doc.add_page_break()
    doc.add_heading("1 Descrição do projeto",level=1)
    doc.add_paragraph("O Hoshi Calendar ajuda uma pessoa a organizar tarefas, compromissos, prazos, lembretes e eventos em uma agenda única. O problema tratado é a perda de contexto quando atividades com duração de vários dias são cadastradas como vários compromissos separados.")
    doc.add_paragraph("O objetivo é registrar uma marcação uma única vez, com início e fim, e permitir sua consulta nas visões diária, semanal e mensal. A visão mensal mostra indicadores; as visões diária e semanal mostram mais detalhes. A pessoa pode editar, concluir, reabrir e excluir suas marcações.")
    doc.add_paragraph("Escopo atual: aplicação local em PHP e MySQL, sem contas individuais. Fora do escopo desta versão: sincronização com outros calendários, envio automático de notificações, colaboração entre pessoas e aplicativo móvel nativo. O serviço de notificações aparece como extensão planejada no modelo de projeto.")
    doc.add_heading("2 Requisitos",level=1)
    doc.add_heading("2.1 Requisitos funcionais",level=2)
    rf=[
        ("RF01","Cadastrar marcação com título, tipo, data inicial e horário."),
        ("RF02","Informar data final opcional para uma marcação que dure vários dias."),
        ("RF03","Listar marcações nas visões diária, semanal e mensal."),
        ("RF04","Exibir uma marcação de período em cada dia abrangido, mantendo um único registro."),
        ("RF05","Visualizar os detalhes das marcações de um dia em painel lateral."),
        ("RF06","Filtrar marcações pelo tipo."),
        ("RF07","Editar título, descrição, tipo, horário e período de uma marcação."),
        ("RF08","Concluir e reabrir uma marcação."),
        ("RF09","Excluir uma marcação após confirmação."),
        ("RF10","Iniciar o cadastro com a data selecionada preenchida."),
        ("RF11","Agendar e enviar lembretes por serviço externo, em evolução futura."),
    ]
    table(doc,["ID","Descrição"],rf,[.7,6.1])
    doc.add_heading("2.2 Requisitos não funcionais",level=2)
    rnf=[
        ("RNF01","Executar em PHP 8 ou superior com MySQL ou MariaDB."),
        ("RNF02","Adaptar o layout a telas de 320 px de largura sem perder ações essenciais."),
        ("RNF03","Manter todas as ações acessíveis por teclado e indicar o foco visivelmente."),
        ("RNF04","Reduzir animações quando o sistema operacional solicitar movimento reduzido."),
        ("RNF05","Validar entradas no servidor e usar consultas SQL parametrizadas."),
        ("RNF06","Proteger ações de escrita contra requisições sem token de sessão válido."),
    ]
    table(doc,["ID","Descrição verificável"],rnf,[.7,6.1])
    doc.add_heading("2.3 Regras de negócio",level=2)
    rn=[
        ("RN01","A data final, quando informada, deve ser igual ou posterior à inicial."),
        ("RN02","Uma marcação ocupa todos os dias do intervalo, inclusive início e fim."),
        ("RN03","Uma marcação de vários dias continua sendo um único registro no banco."),
        ("RN04","Os tipos permitidos são tarefa, compromisso, prazo, lembrete, evento e outro."),
        ("RN05","Marcações antigas sem data final valem somente no dia inicial."),
        ("RN06","A conclusão pode ser revertida por meio da ação Reabrir."),
        ("RN07","A exclusão é definitiva e exige confirmação na interface."),
    ]
    table(doc,["ID","Regra"],rn,[.7,6.1])
    doc.add_page_break()
    doc.add_heading("3 Diagrama de casos de uso",level=1)
    doc.add_paragraph("O usuário é o ator da versão implementada. O serviço externo de notificações representa a extensão planejada. A validação de período é incluída nos fluxos de criação e edição.")
    doc.add_picture(str(use),width=Inches(6.75))
    doc.add_paragraph("Figura 1  Casos de uso dentro da fronteira do Hoshi Calendar")
    add_case(doc,"UC01","Cadastrar marcação","Usuário","A aplicação e o banco estão disponíveis.","1. Abrir Nova marcação. 2. Informar título, tipo, data inicial, horário e, se necessário, data final. 3. Salvar. O sistema valida o período e grava um registro.","O usuário deixa a data final vazia; a marcação vale apenas no dia inicial.","Título vazio, data inválida, tipo desconhecido ou fim anterior ao início impedem o cadastro.","A marcação aparece nos dias abrangidos.")
    add_case(doc,"UC02","Consultar calendário","Usuário","O calendário está aberto.","1. Escolher dia, semana ou mês. 2. Navegar pelo período. 3. Selecionar um dia para ver os detalhes. O sistema consulta marcações que cruzam o intervalo exibido.","O usuário filtra por tipo ou navega para hoje.","Falha de conexão impede a consulta e deve ser informada.","As marcações do período são apresentadas sem alterar dados.")
    add_case(doc,"UC03","Alterar status","Usuário","Existe uma marcação cadastrada.","1. Abrir os detalhes. 2. Selecionar Concluir. O sistema atualiza o status.","Se já estiver concluída, o usuário seleciona Reabrir.","ID inexistente ou sessão inválida impedem a atualização.","O estado novo aparece na agenda.")
    add_case(doc,"UC04","Excluir marcação","Usuário","Existe uma marcação cadastrada.","1. Abrir os detalhes. 2. Selecionar Excluir. 3. Confirmar. O sistema apaga o registro.","O usuário cancela a confirmação e nenhum dado é alterado.","ID inexistente ou sessão inválida impedem a exclusão.","A marcação deixa de aparecer em todo o período.")
    doc.add_page_break()
    doc.add_heading("4 Diagrama de classes de projeto",level=1)
    doc.add_paragraph("O diagrama representa o projeto conceitual completo. A versão inicial em PHP usa funções e páginas no lugar de classes concretas; Usuario, Calendario, Notificacao e ServicoNotificacao indicam a estrutura prevista para a evolução. Marcacao e TipoMarcacao correspondem ao registro e ao catálogo de tipos já usados.")
    doc.add_picture(str(classes),width=Inches(6.85))
    doc.add_paragraph("Figura 2  Classes, atributos, métodos, tipos, visibilidades e multiplicidades")
    doc.add_paragraph("Relacionamentos: Usuario 1 a 1 Calendario (associação); Calendario 1 a 0..* Marcacao (composição); Marcacao 1 a 1 TipoMarcacao (associação); Marcacao 1 a 0..* Notificacao (composição planejada); Notificacao 0..* a 1 ServicoNotificacao (associação planejada).")
    doc.add_heading("5 Repositório",level=1)
    doc.add_paragraph("Código fonte e histórico de commits: https://github.com/F3lipeMelo/calendario-php")
    doc.add_paragraph("A evolução desta versão foi desenvolvida na branch feat/itens-periodos-categorias. O vídeo demonstrativo de até um minuto será preparado pelo integrante.")
    path=DOCS/"documentacao-hoshi-calendar.docx";doc.save(path);print(path)

if __name__=="__main__": build()

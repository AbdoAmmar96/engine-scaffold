import { useEffect, useRef } from "react";

/**
 * HeroWebGL — خلفية الهيرو لما نمط الهيرو = webgl.
 * شادر WebGL خام (بدون three.js) بيرسم بقع ضوئية ذهبي/كحلي بتتحرك ببطء فوق الخلفية الداكنة.
 * - بيقرأ الألوان من CSS variables ⇒ بيتبع الثيم من الداشبورد تلقائيًا
 * - prefers-reduced-motion ⇒ فريم ثابت واحد
 * - DPR cap 1.5 + إيقاف الرندر لما السكشن يخرج من الشاشة أو التاب يتخفي
 * - لو WebGL مش مدعوم بيرجع null والخلفية العادية بتفضل مكانه
 * (variant الجسيمات بـ three.js بيتضاف في المرحلة 7 بنفس الـ slot)
 */

const FRAG = `
precision mediump float;
uniform vec2 u_res;
uniform float u_time;
uniform vec3 u_base;
uniform vec3 u_gold;
uniform vec3 u_navy;

float blob(vec2 uv, vec2 c, float r) {
    return smoothstep(r, 0.0, distance(uv, c));
}

void main() {
    vec2 uv = gl_FragCoord.xy / u_res;
    uv.x *= u_res.x / u_res.y;
    float t = u_time * 0.12;

    vec2 c1 = vec2(0.75 + 0.18 * sin(t),        0.68 + 0.14 * cos(t * 0.8));
    vec2 c2 = vec2(0.22 + 0.16 * cos(t * 0.7),  0.30 + 0.16 * sin(t * 0.9));
    vec2 c3 = vec2(0.52 + 0.22 * sin(t * 0.5),  0.85 + 0.10 * cos(t * 0.6));

    c1.x *= u_res.x / u_res.y;
    c2.x *= u_res.x / u_res.y;
    c3.x *= u_res.x / u_res.y;

    vec3 col = u_base;
    col = mix(col, u_gold, blob(uv, c1, 0.55) * 0.30);
    col = mix(col, u_navy, blob(uv, c2, 0.60) * 0.45);
    col = mix(col, u_gold, blob(uv, c3, 0.45) * 0.16);

    gl_FragColor = vec4(col, 1.0);
}`;

const VERT = `
attribute vec2 p;
void main() { gl_Position = vec4(p, 0.0, 1.0); }`;

function cssColor(name: string, fallback: [number, number, number]): [number, number, number] {
    const raw = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    const m = raw.match(/^#([0-9a-f]{6})$/i);
    if (!m) return fallback;
    const n = parseInt(m[1], 16);
    return [((n >> 16) & 255) / 255, ((n >> 8) & 255) / 255, (n & 255) / 255];
}

export default function HeroWebGL() {
    const canvasRef = useRef<HTMLCanvasElement>(null);

    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas) return;

        const gl = canvas.getContext("webgl", { antialias: true, alpha: false });
        if (!gl) return;

        // لازم نفحص COMPILE_STATUS: من غيره شادر بايظ بيعدّي ويرسم كانفس أسود
        // على بعض كروت الشاشة بدل ما يرجع null وتفضل الخلفية العادية مكانه
        const compile = (type: number, src: string) => {
            const shader = gl.createShader(type);
            if (!shader) return null;

            gl.shaderSource(shader, src);
            gl.compileShader(shader);

            if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
                gl.deleteShader(shader);
                return null;
            }

            return shader;
        };

        const vert = compile(gl.VERTEX_SHADER, VERT);
        const frag = compile(gl.FRAGMENT_SHADER, FRAG);
        const prog = gl.createProgram();

        // أي فشل هنا = منظّف ورا نفسه ونسيب الخلفية العادية
        const abort = () => {
            if (vert) gl.deleteShader(vert);
            if (frag) gl.deleteShader(frag);
            if (prog) gl.deleteProgram(prog);
            gl.getExtension("WEBGL_lose_context")?.loseContext();
        };

        if (!vert || !frag || !prog) {
            abort();
            return;
        }

        gl.attachShader(prog, vert);
        gl.attachShader(prog, frag);
        gl.linkProgram(prog);

        if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) {
            abort();
            return;
        }

        gl.useProgram(prog);
        // اتلحمت في البرنامج خلاص، فمفيش داعي نستنى بيهم
        gl.deleteShader(vert);
        gl.deleteShader(frag);

        const buf = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, buf);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);
        const loc = gl.getAttribLocation(prog, "p");
        gl.enableVertexAttribArray(loc);
        gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);

        const uRes = gl.getUniformLocation(prog, "u_res");
        const uTime = gl.getUniformLocation(prog, "u_time");
        gl.uniform3fv(gl.getUniformLocation(prog, "u_base"), cssColor("--bg-dark", [0.043, 0.071, 0.125]));
        gl.uniform3fv(gl.getUniformLocation(prog, "u_gold"), cssColor("--primary", [0.788, 0.635, 0.153]));
        gl.uniform3fv(gl.getUniformLocation(prog, "u_navy"), cssColor("--secondary", [0.118, 0.227, 0.373]));

        const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
        const resize = () => {
            const { clientWidth: w, clientHeight: h } = canvas;
            canvas.width = Math.round(w * dpr);
            canvas.height = Math.round(h * dpr);
            gl.viewport(0, 0, canvas.width, canvas.height);
            gl.uniform2f(uRes, canvas.width, canvas.height);
        };
        resize();
        window.addEventListener("resize", resize);

        const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        let raf = 0;
        let visible = true;
        const start = performance.now();

        const frame = () => {
            gl.uniform1f(uTime, (performance.now() - start) / 1000);
            gl.drawArrays(gl.TRIANGLES, 0, 3);
            if (!reduced && visible && !document.hidden) raf = requestAnimationFrame(frame);
        };

        const io = new IntersectionObserver(([e]) => {
            visible = e.isIntersecting;
            if (visible && !reduced) {
                cancelAnimationFrame(raf);
                raf = requestAnimationFrame(frame);
            }
        });
        io.observe(canvas);

        const onVis = () => {
            if (!document.hidden && visible && !reduced) {
                cancelAnimationFrame(raf);
                raf = requestAnimationFrame(frame);
            }
        };
        document.addEventListener("visibilitychange", onVis);

        frame();

        return () => {
            cancelAnimationFrame(raf);
            io.disconnect();
            window.removeEventListener("resize", resize);
            document.removeEventListener("visibilitychange", onVis);

            // من غير التنظيف ده كل تنقّل جوه الـ SPA بيسيب WebGL context مفتوح،
            // والمتصفح بيقفل أقدم context بعد ~16 فيتبوّظ اللي شغال
            gl.deleteBuffer(buf);
            gl.deleteProgram(prog);
            gl.getExtension("WEBGL_lose_context")?.loseContext();
        };
    }, []);

    return <canvas ref={canvasRef} className="pointer-events-none absolute inset-0 h-full w-full" aria-hidden="true" />;
}

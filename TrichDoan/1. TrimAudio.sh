for i in ../ogg/*.m4a; do ffmpeg -n -i "$i" "${i%.*}.mp3"; done
for i in ../ogg/*.ogg; do ffmpeg -n -i "$i" "${i%.*}.mp3"; done
for i in ../ogg/*.mp3; do echo $i; done
echo 'FileName:'
read open
echo 'From [00:00:10]:'
read from
echo 'To [00:01:00]:'
read to
for i in *.ogg; do ffmpeg -n -i "$i" "../ogg/${i%.*}.mp3"; done
ffmpeg -ss $from -to $to -i ../ogg/$open ${open%.*}.mp3
ffmpeg -hide_banner -loop 1 -i ../img/cover_big1.png -i ${open%.*}.mp3 -c:v libx264 -preset ultrafast -tune stillimage -c:a copy -pix_fmt yuv420p -shortest -vf scale=720:-2 -vf  subtitles=../subtitle/tieude.srt:force_style='MarginV=250' ${open%.*}.mp4
#ffmpeg -hide_banner -loop 1 -i ../img/cover_big1.png -i ${open%.*}.mp3 -c:v libx264 -preset ultrafast -tune stillimage -c:a copy -pix_fmt yuv420p -shortest -vf scale=720:-2 ${open%.*}.mp4
read open2
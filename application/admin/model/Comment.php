<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/28
 * Time: 20:39
 */

namespace app\admin\model;


use think\Db;
use think\Model;

//后台评论列表
class Comment extends Model
{
    //时间自动完成
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created';
    protected $updateTime = false;

    //评论类型的获取器
    public function getCommentTypeAttr($value)
    {
        $status = [
            'News' => '新闻',
            'Blog' => '博客'
        ];
        return $status[$value];
    }

    //获取评论的所属内容(当前模型为 Comment)
    public function target()
    {
        //SQL select * from tedu_BLOG where id=1
        //select * from tedu_NEWS where id=1
        //方式一:
        $res = $this->morphTo('comment', [
            '博客' => 'app\admin\model\Blog',
            '新闻' => 'app\admin\model\News',
        ]);
        return $res;

        //方式二:
//        return $this->morphTo(
//            ['comment_type', 'comment_id'],
//            ['博客' => 'app\admin\model\Blog', '新闻' => 'app\admin\model\News',]
//        );
    }

    /**评论列表
     * @return array
     */
    public function getList()
    {
        $where     = [];
        $newsWhere = [];
        $cond      = input('param.');
        if (isset($cond['content']) && !empty($cond['content'])) {
            $where['content'] = ['like', '%' . $cond['content'] . '%'];
        }

        //按照所属内容查询，表里是数字，页面为汉字
        if (isset($cond['title']) && !empty($cond['title'])) {
//            halt($cond['title']); //博
            //从父表 tedu_blog 通过条件查询
            $where1['title'] = ['like', '%' . $cond['title'] . '%'];
            $arr             = Db::name('blog')->field('id')
                ->where($where1)->select();
//            halt($blogIdArr);
            //将查询出的 数组对象 转为数组
            $blogIdarr = [];
            if ($arr) {
                foreach ($arr as $k => $v) {
                    $blogIdarr[] = $v['id'];
                }
                $where['comment_id']   = ['in', $blogIdarr];
                $where['comment_type'] = ['eq', 'blog'];
//                halt($where);
            }

            $where2['title'] = ['like', '%' . $cond['title'] . '%'];
            $newsArr             = Db::name('news')->field('id')
                ->where($where2)->select();
            $newsIdArr       = [];
            //将查询出的 数组对象 转为数组
            if ($newsArr) {
                foreach ($newsArr as $k => $v) {
                    $newsIdArr[] = $v['id'];
                }
                $newsWhere['comment_id']   = ['in', $newsIdArr];
                $newsWhere['comment_type'] = ['eq', 'news'];
            }
//            halt($newsWhere);
        }

        //方式一：在模板文件通过js转换格式 /18_news/tp5/public/admin/comment/index/comment_id/username/content/tom
        //注意表单提交方式为 button
//        return $this->field('*')->where($where)->order('created DESC')
//            ->paginate(5);//默认调用系统的分页配置

        //方式二：通过框架自带 paginate() 方法实现
        //注意表单提交方式为 submit
//        return $this->field('*')->where($where)->whereOr($newsWhere)->order('created DESC')
//            ->paginate(5, false, ['query' => request()->param()]);//默认调用系统的分页配置

        $res = $this->field('*')->where($where)->where($newsWhere)->order('created DESC')
            ->fetchSql()->select();
        halt($res);
        //SELECT * FROM `tedu_comment` WHERE  `comment_type` = 'blog'  AND `comment_id` IN (31,18) OR `comment_type` = 'news' ORDER BY `created` DESC'
    }

    //获取评论的作者
    public function author()
    {
        return $this->belongsTo('User', 'uid');
    }

}